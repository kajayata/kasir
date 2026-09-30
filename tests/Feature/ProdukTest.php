<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdukTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->kasir = User::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function data(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Mi Instan Goreng',
            'code' => 'BRG900',
            'barcode' => '899999999999',
            'price' => 3200,
            'stock' => 50,
        ], $overrides);
    }

    public function test_daftar_produk_dapat_diakses(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('kasir.produk.index'))
            ->assertOk()
            ->assertSee('Indomie Goreng')
            ->assertSee('BRG001');
    }

    public function test_daftar_produk_dapat_difilter(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('kasir.produk.index', ['q' => 'beras']))
            ->assertOk()
            ->assertSee('Beras 5 Kg')
            ->assertDontSee('Indomie Goreng');
    }

    public function test_produk_baru_tersimpan(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('kasir.produk.store'), $this->data())
            ->assertRedirect(route('kasir.produk.index'));

        $this->assertDatabaseHas('products', ['code' => 'BRG900', 'name' => 'Mi Instan Goreng', 'price' => 3200, 'stock' => 50]);
    }

    public function test_kode_dan_barcode_duplikat_ditolak(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('kasir.produk.store'), $this->data(['code' => 'BRG001']))
            ->assertSessionHasErrors('code');

        $this->actingAs($this->kasir)
            ->post(route('kasir.produk.store'), $this->data(['barcode' => '899123456001']))
            ->assertSessionHasErrors('barcode');

        $this->assertSame(6, Product::count());
    }

    public function test_produk_tanpa_barcode_boleh_berlebihan(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('kasir.produk.store'), $this->data(['barcode' => '']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->kasir)
            ->post(route('kasir.produk.store'), $this->data(['code' => 'BRG901', 'barcode' => null]))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Product::whereNull('barcode')->count());
    }

    public function test_harga_negatif_ditolak(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('kasir.produk.store'), $this->data(['price' => -1]))
            ->assertSessionHasErrors('price');
    }

    public function test_produk_bisa_diubah(): void
    {
        $produk = Product::where('code', 'BRG001')->firstOrFail();

        $this->actingAs($this->kasir)
            ->put(route('kasir.produk.update', $produk), $this->data([
                'code' => $produk->code,
                'barcode' => $produk->barcode,
                'price' => 3600,
                'stock' => 7,
            ]))
            ->assertRedirect(route('kasir.produk.index'));

        $this->assertSame(3600, $produk->fresh()->price);
        $this->assertSame(7, $produk->fresh()->stock);
    }

    public function test_produk_bisa_dihapus(): void
    {
        $produk = Product::where('code', 'BRG001')->firstOrFail();

        $this->actingAs($this->kasir)
            ->delete(route('kasir.produk.destroy', $produk))
            ->assertRedirect(route('kasir.produk.index'));

        $this->assertDatabaseMissing('products', ['id' => $produk->id]);
    }

    public function test_halaman_produk_hanya_untuk_user_login(): void
    {
        $this->get(route('kasir.produk.index'))->assertRedirect(route('login'));
        $this->get(route('kasir.produk.create'))->assertRedirect(route('login'));
        $this->post(route('kasir.produk.store'), $this->data())->assertRedirect(route('login'));
    }
}
