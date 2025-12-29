<?php

final class Invoice
{
    private bool $paid;

    private function __construct(private readonly string $id, private readonly string $orderId, private readonly int $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Invoice amount cannot be negative.');
        }
        $this->paid = false;
    }

    public static function create(string $orderId, int $amount): self
    {
        $id = uniqid('invoice_', true);
        return new self($id, $orderId, $amount);
    }

    public static function createForOrder(string $orderId, int $amount): self
    {
        return new self($orderId, $amount);
    }

    public static function reconstitute(string $id, string $orderId, int $amount, bool $paid): self
    {
        $invoice = new self($id, $orderId, $amount);
        $invoice->paid = $paid;
        return $invoice;
    }

    public function getId(): string { return $this->id; }
    public function getOrderId(): string { return $this->orderId; }
    public function getAmount(): int { return $this->amount; }
    public function isPaid(): bool { return $this->paid; }

    public function markPaid(): void
    {
        if ($this->paid) {
            throw new LogicException('Invoice already paid');
        }

        $this->paid = true;
    }
}
