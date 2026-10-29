<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TransaksiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function payload(array $overrides = []): array
    {
        $indomie = Product::where('code', 'BRG001')->firstOrFail();
        $beras = Product::where('code', 'BRG004')->firstOrFail();

        return array_merge([
            'items' => [
                ['id' => $indomie->id, 'qty' => 2],
                ['id' => $beras->id, 'qty' => 1],
            ],
            'discount_percent' => 0,
            'discount_amount' => 0,
            'tax' => 0,
            'other_fee' => 0,
            'paid_amount' => 100000,
            'payment_method' => 'Tunai',
            'customer_id' => null,
        ], $overrides);
    }

    public function test_total_dihitung_server_dari_harga_database_bukan_kiriman_client(): void
    {
        $kasir = User::factory()->create();

        // Client diam-diam mencoba mengarang harga sendiri.
        $response = $this->actingAs($kasir)->postJson(route('kasir.store'), $this->payload([
            'items' => [['id' => 1, 'qty' => 2, 'price' => 1, 'name' => '<script>alert(1)</script>']],
            'paid_amount' => 7000,
        ]));

        $response->assertOk();

        $transaction = Transaction::sole();

        // 2 x 3.500 = 7.000, bukan 2 x 1 = 2.
        $this->assertSame(7000, $transaction->subtotal);
        $this->assertSame(7000, $transaction->grand_total);
        $this->assertSame(0, $transaction->change_amount);
        $this->assertSame('Indomie Goreng', $transaction->details->sole()->product_name);
    }

    public function test_diskon_pajak_dan_biaya_terhitung_benar(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->postJson(route('kasir.store'), $this->payload([
            'discount_percent' => 10,
            'discount_amount' => 2000,
            'tax' => 1000,
            'other_fee' => 500,
            'paid_amount' => 100000,
        ]))->assertOk();

        $transaction = Transaction::sole();

        // subtotal 82.000 (2 x 3.500 + 1 x 75.000), diskon 8.200 + 2.000 = 10.200
        $this->assertSame(82000, $transaction->subtotal);
        $this->assertSame(10200, $transaction->discount);
        $this->assertSame(73300, $transaction->grand_total);   // 82.000 - 10.200 + 1.000 + 500
        $this->assertSame(26700, $transaction->change_amount);
    }

    public function test_uang_kurang_ditolak(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)
            ->postJson(route('kasir.store'), $this->payload(['paid_amount' => 1000]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('paid_amount');

        $this->assertSame(0, Transaction::count());
    }

    public function test_stok_kurang_ditolak_dan_tidak_dipotong(): void
    {
        $kasir = User::factory()->create();
        $beras = Product::where('code', 'BRG004')->firstOrFail();

        $this->actingAs($kasir)
            ->postJson(route('kasir.store'), $this->payload([
                'items' => [['id' => $beras->id, 'qty' => $beras->stock + 1]],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('items');

        $this->assertSame(0, Transaction::count());
        $this->assertSame($beras->stock, $beras->fresh()->stock);
    }

    public function test_pembayaran_memotong_stok_dan_menghasilkan_nomor_unik(): void
    {
        $kasir = User::factory()->create();
        $indomie = Product::where('code', 'BRG001')->firstOrFail();
        $stokAwal = $indomie->stock;

        $this->actingAs($kasir)->postJson(route('kasir.store'), $this->payload())->assertOk();
        $this->actingAs($kasir)->postJson(route('kasir.store'), $this->payload())->assertOk();

        $nomor = Transaction::pluck('transaction_number');

        $this->assertCount(2, $nomor);
        $this->assertCount(2, $nomor->unique());
        $this->assertStringStartsWith('TRX-'.now()->format('Ymd'), $nomor->first());
        $this->assertSame($stokAwal - 4, $indomie->fresh()->stock);
    }

    public function test_tahan_lalu_lanjutkan_menjadi_transaksi_bayar(): void
    {
        $kasir = User::factory()->create();
        $indomie = Product::where('code', 'BRG001')->firstOrFail();
        $stokAwal = $indomie->stock;

        // Tahan: tersimpan, tanpa nomor, tanpa potong stok.
        $holdId = $this->actingAs($kasir)
            ->postJson(route('kasir.hold'), $this->payload())
            ->assertOk()
            ->json('id');

        $ditahan = Transaction::findOrFail($holdId);
        $this->assertSame(Transaction::STATUS_HELD, $ditahan->status);
        $this->assertNull($ditahan->transaction_number);
        $this->assertSame($stokAwal, $indomie->fresh()->stock);

        // Muat ulang isinya, lalu lanjutkan pembayaran.
        $this->getJson(route('kasir.detail', $ditahan))->assertOk()->assertJsonCount(2, 'items');

        $this->actingAs($kasir)
            ->postJson(route('kasir.resume', $ditahan), $this->payload())
            ->assertOk();

        $ditahan->refresh();
        $this->assertSame(Transaction::STATUS_PAID, $ditahan->status);
        $this->assertNotNull($ditahan->transaction_number);
        $this->assertSame($stokAwal - 2, $indomie->fresh()->stock);
        $this->assertSame(2, $ditahan->details()->count());
    }

    public function test_pembayaran_mengirim_event_order_placed(): void
    {
        Event::fake([OrderPlaced::class]);

        $kasir = User::factory()->create();

        $this->actingAs($kasir)->postJson(route('kasir.store'), $this->payload())->assertOk();

        Event::assertDispatched(OrderPlaced::class, function (OrderPlaced $event) use ($kasir) {
            $payload = $event->broadcastWith();

            return $event->broadcastAs() === 'order.placed'
                && $event->broadcastOn()->name === 'orders'
                && $event->transaction->transaction_number !== null
                && $event->transaction->user->is($kasir)
                && $payload['grand_total'] === 82000
                && $payload['kasir'] === $kasir->name;
        });
    }

    public function test_kegagalan_broadcast_tidak_menggagalkan_transaksi(): void
    {
        Broadcast::extend('throwing', fn () => new class implements Broadcaster
        {
            public function auth($request) {}

            public function validAuthenticationResponse($request, $result) {}

            public function broadcast(array $channels, $event, array $payload = [])
            {
                throw new BroadcastException('Reverb tidak tersedia.');
            }
        });

        config([
            'broadcasting.default' => 'throwing',
            'broadcasting.connections.throwing' => ['driver' => 'throwing'],
        ]);

        $kasir = User::factory()->create();

        $this->actingAs($kasir)->postJson(route('kasir.store'), $this->payload())->assertOk();

        $this->assertSame(1, Transaction::count());
        $this->assertNotNull(Transaction::sole()->transaction_number);
    }

    public function test_transaksi_ditahan_tidak_mengirim_event_order_placed(): void
    {
        Event::fake([OrderPlaced::class]);

        $kasir = User::factory()->create();

        $this->actingAs($kasir)
            ->postJson(route('kasir.hold'), $this->payload())
            ->assertOk();

        Event::assertNotDispatched(OrderPlaced::class);
    }

    public function test_lanjutkan_transaksi_ditahan_mengirim_event_order_placed(): void
    {
        Event::fake([OrderPlaced::class]);

        $kasir = User::factory()->create();

        $holdId = $this->actingAs($kasir)
            ->postJson(route('kasir.hold'), $this->payload())
            ->assertOk()
            ->json('id');

        Event::assertNotDispatched(OrderPlaced::class);

        $this->actingAs($kasir)
            ->postJson(route('kasir.resume', Transaction::findOrFail($holdId)), $this->payload())
            ->assertOk();

        Event::assertDispatchedTimes(OrderPlaced::class, 1);
    }

    public function test_transaksi_bayar_tidak_bisa_dibatalkan(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->postJson(route('kasir.store'), $this->payload())->assertOk();

        $this->actingAs($kasir)
            ->postJson(route('kasir.cancel', Transaction::sole()))
            ->assertStatus(409);
    }

    public function test_halaman_kasir_hanya_untuk_user_login(): void
    {
        $this->get(route('kasir'))->assertRedirect(route('login'));
        $this->postJson(route('kasir.store'), $this->payload())->assertUnauthorized();
    }
}
