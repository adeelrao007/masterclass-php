<?php
final class OrderStatus
{
    private function __construct(
        private readonly string $value
    ) {}

    public static function created(): self
    {
        return new self('created');
    }

    public function pay(): self
    {
        $this->assertState('created');

        return new self('paid');
    }

    public function ship(): self
    {
        $this->assertState('paid');

        return new self('shipped');
    }

    public function complete(): self
    {
        $this->assertState('shipped');

        return new self('completed');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function canBePaid(): bool
    {
        return $this->value === 'created';
    }

    public function isPaid(): bool
    {
        return $this->value === 'paid' || $this->value === 'shipped' || $this->value === 'completed';
    }

    public function isShipped(): bool
    {
        return $this->value === 'shipped' || $this->value === 'completed';
    }

    public function isCompleted(): bool
    {
        return $this->value === 'completed';
    }

    public function isRefundable(): bool
    {
        return in_array($this->value, ['paid', 'shipped'], true);
    }

    private function assertState(string $expected): void
    {
        if ($this->value !== $expected) {
            throw new LogicException(
                "Invalid transition from {$this->value}"
            );
        }
    }
}