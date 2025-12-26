<?php

final class Invoice
{
    private bool $paid;

    public function __construct(private readonly string $id, private readonly string $orderId, private readonly int $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Invoice amount cannot be negative.');
        }
        $this->paid = false;
    }

    public function getId(): string { return $this->id; }
    public function getOrderId(): string { return $this->orderId; }
    public function getAmount(): int { return $this->amount; }
    public function isPaid(): bool { return $this->paid; }

    public function markPaid(): void
    {
        if ($this->paid) {
            throw new LogicException('Invoice already paid.');
        }
        $this->paid = true;
    }
}
