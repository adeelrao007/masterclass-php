<?php

final class Customer
{
    private bool $active;
    private string $name;

    private function __construct(
        private readonly string $id,
        string $name,
        bool $active
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Customer name required');
        }

        $this->name = $name;
        $this->active = $active;
    }

    public static function create(string $name): self
    {
        return new self(
            uniqid('customer_', true),
            $name,
            true
        );
    }

    public static function reconstitute(
        string $id,
        string $name,
        bool $active
    ): self {
        return new self($id, $name, $active);
    }

    public function rename(string $newName): void
    {
        if (trim($newName) === '') {
            throw new InvalidArgumentException('Customer name required');
        }

        if ($this->name === $newName) {
            return;
        }

        $this->name = $newName;
    }

    public function deactivate(): void
    {
        if (! $this->active) {
            throw new LogicException('Customer already inactive');
        }

        $this->active = false;
    }

    public function activate(): void
    {
        if ($this->active) {
            throw new LogicException('Customer already active');
        }

        $this->active = true;
    }
}
