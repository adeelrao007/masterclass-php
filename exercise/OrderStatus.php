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

    private function assertState(string $expected): void
    {
        if ($this->value !== $expected) {
            throw new LogicException(
                "Invalid transition from {$this->value}"
            );
        }
    }
}