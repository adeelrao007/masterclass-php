<?php
final class Money
{
    private readonly string $currency;
    private int $amount;

    public function __construct(int $amount, string $currency)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException("Amount cannot be negative.");
        }
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        if ($other->currency !== $this->currency) {
            throw new InvalidArgumentException("Currency mismatch.");
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        if ($other->currency !== $this->currency) {
            throw new InvalidArgumentException("Currency mismatch.");
        }
        $result = $this->amount - $other->amount;
        if ($result < 0) {
            throw new InvalidArgumentException("Resulting amount cannot be negative.");
        }
        return new self($result, $this->currency);
    }
}