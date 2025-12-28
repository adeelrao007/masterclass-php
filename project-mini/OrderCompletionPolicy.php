<?php

final class OrderCompletionPolicy
{
    public function canShip(Order $order): bool
    {
        return $order->getStatus()->isPaid();
    }

    public function canComplete(Order $order): bool
    {
        return $order->getStatus()->isShipped();
    }
}