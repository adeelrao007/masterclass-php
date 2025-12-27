<?php

final class RefundPolicy
{
    public function canRefund(Order $order): bool
    {
        return in_array(
            $order->getStatus()->value(),
            ['paid', 'shipped']
        );
    }
}