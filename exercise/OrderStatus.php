<?php
final class OrderStatus
{
    private const CREATED = 'created';
    private const PAID = 'paid';
    private const SHIPPED = 'shipped';
    private const COMPLETED = 'completed';

    private static array $transitions = [
        self::CREATED => [self::PAID],
        self::PAID => [self::SHIPPED],
        self::SHIPPED => [self::COMPLETED],
        self::COMPLETED => [],
    ];

    private string $status;

    public function __construct(string $status = self::CREATED)
    {
        if (!isset(self::$transitions[$status])) {
            throw new InvalidArgumentException("Invalid initial status.");
        }
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function advance(string $nextStatus): void
    {
        $allowed = self::$transitions[$this->status] ?? [];
        if (!in_array($nextStatus, $allowed, true)) {
            throw new LogicException("Invalid status transition from {$this->status} to {$nextStatus}.");
        }
        $this->status = $nextStatus;
    }
}