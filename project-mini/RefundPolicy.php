<?php

final class RefundPolicy
{
    public function canRefund(Order $order): bool
    {
        return $order->getStatus()->isPaid() || $order->getStatus()->isShipped();
    }
}