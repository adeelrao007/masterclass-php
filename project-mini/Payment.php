<?php

final class Payment
{
    private bool $processed;

    public function __construct(private readonly string $id, private readonly string $invoiceId, private readonly int $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Payment amount cannot be negative.');
        }
        $this->processed = false;
    }

    public function getId(): string { return $this->id; }
    public function getInvoiceId(): string { return $this->invoiceId; }
    public function getAmount(): int { return $this->amount; }
    public function isProcessed(): bool { return $this->processed; }

    public function process(int $expectedAmount): void
    {
        if ($this->processed) {
            throw new LogicException('Payment already processed.');
        }
        if ($this->amount !== $expectedAmount) {
            throw new InvalidArgumentException('Payment amount does not match invoice.');
        }
        $this->processed = true;
    }
}
