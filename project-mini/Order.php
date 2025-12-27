<?php

final class Order
{
    private OrderStatus $status;

    public function __construct(private readonly string $id, private readonly string $customerId, private readonly int $total)
    {
        if ($total < 0) {
            throw new InvalidArgumentException('Order total cannot be negative.');
        }
        $this->status = OrderStatus::created();
    }

    public function getId(): string { return $this->id; }
    public function getCustomerId(): string { return $this->customerId; }
    public function getStatus(): OrderStatus { return $this->status; }
    public function getTotal(): int { return $this->total; }

    public function pay(): void
    {
        $this->status = $this->status->pay();
    }

    public function ship(): void
    {
        $this->status = $this->status->ship();
    }

    public function complete(): void
    {
        $this->status = $this->status->complete();
    }
}
