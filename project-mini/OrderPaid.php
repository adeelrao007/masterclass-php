<?php

final class OrderPaid
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $paymentId
    ) {}
}