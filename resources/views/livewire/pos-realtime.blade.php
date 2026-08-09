<div wire:poll.10s="refreshData" class="sr-only" aria-live="polite">
    <span>{{ $orders->count() }} transaksi terbaru tersinkronisasi.</span>
    @foreach($orders as $order)
        <span wire:key="pos-order-{{ $order->id }}">{{ $order->order_number }}:{{ $order->status }}</span>
    @endforeach
</div>
