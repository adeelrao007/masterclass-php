<?php

final class Order
{
    private OrderStatus $status;
    private array $events = [];

    private function __construct(
        private readonly string $id, 
        private readonly string $customerId, 
        private readonly int $total
    )
    {
        if ($total < 0) {
            throw new InvalidArgumentException('Order total cannot be negative.');
        }
        $this->status = OrderStatus::created();
    }

    public static function create(string $customerId, int $total): self
    {
        $id = uniqid('order_', true);
        return new self($id, $customerId, $total);
    }

    public static function reconstitute(string $id, string $customerId, int $total, OrderStatus $status): self
    {
        $order = new self($id, $customerId, $total);
        $order->status = $status;
        return $order;
    }

    public function getId(): string { return $this->id; }
    public function getCustomerId(): string { return $this->customerId; }
    public function getStatus(): OrderStatus { return $this->status; }
    public function getTotal(): int { return $this->total; }

    public function pay(PaymentId $paymentId): void
    {
        if (! $this->status->canBePaid()) {
            throw new LogicException('Order cannot be paid');
        }
        $this->status = $this->status->pay();
        $this->recordEvent(new OrderPaid($this->id, $paymentId->value) );
    }

    private function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    public function ship(): void
    {
        $this->status = $this->status->ship();
    }

    public function complete(): void
    {
        $this->status = $this->status->complete();
    }

    public function isPaid(): bool
    {
        return $this->status->isPaid();
    }
}
