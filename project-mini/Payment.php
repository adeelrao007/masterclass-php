<?php

final class Payment
{
    private bool $processed;

    private function __construct(private readonly string $id, private readonly string $invoiceId, private readonly int $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Payment amount cannot be negative.');
        }
        $this->processed = false;
    }

    public static function create(string $invoiceId, int $amount): self
    {
        $id = uniqid('payment_', true);
        return new self($id, $invoiceId, $amount);
    }

    public static function reconstitute(string $id, string $invoiceId, int $amount, bool $processed): self
    {
        $payment = new self($id, $invoiceId, $amount);
        $payment->processed = $processed;
        return $payment;
    }

    public function getId(): string { return $this->id; }
    public function getInvoiceId(): string { return $this->invoiceId; }
    public function getAmount(): int { return $this->amount; }
    public function isProcessed(): bool { return $this->processed; }

    public function markProcessed(int $expectedAmount): void
    {
        if ($this->processed) {
            throw new LogicException('Payment already processed.');
        }
        if ($this->amount !== $expectedAmount) {
            throw new InvalidArgumentException('Payment amount does not match invoice.');
        }
        $this->processed = true;
    }

    public function matchesInvoice(Invoice $invoice): bool
    {
        return $this->invoiceId === $invoice->getId() && $this->amount === $invoice->getAmount();
    }
}
