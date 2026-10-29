<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Transaction $transaction)
    {
        $this->transaction->loadMissing('user');
    }

    public function broadcastOn(): Channel
    {
        return new Channel('orders');
    }

    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'grand_total' => $this->transaction->grand_total,
            'payment_method' => $this->transaction->payment_method,
            'kasir' => $this->transaction->user?->name ?? '-',
            'struk_url' => route('kasir.struk', $this->transaction),
        ];
    }
}
