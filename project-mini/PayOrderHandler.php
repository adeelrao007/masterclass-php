<?php

final class PayOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private EventDispatcher $events
    ) {}

    public function handle(string $orderId, string $paymentId): void
    {
        $order = $this->orders->getById($orderId);

        $order->pay($paymentId);

        $this->orders->save($order);

        $this->events->dispatchAll(
            $order->pullEvents()
        );
    }
}