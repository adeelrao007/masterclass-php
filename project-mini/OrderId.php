<?php

final class OrderId
{
    private function __construct(private readonly string $id) {}

    public static function generate(): self
    {
        return new self(uniqid('order_', true));
    }

    public function __toString(): string
    {
        return $this->id;
    }
}