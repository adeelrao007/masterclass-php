<?php

final class PayOrderHandler
{
    public function handle(OrderId $orderId, PaymentId $paymentId): void
    {
        DB::transaction(function () use ($orderId, $paymentId) {
            $order = $this->orders->byId($orderId);

            $order->pay($paymentId);

            $this->orders->save($order);
        });

        $this->dispatcher->dispatchAll(
            $order->pullEvents()
        );
    }
}