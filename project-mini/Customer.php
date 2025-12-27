<?php

final class Customer
{

    private function __construct(
        private readonly string $id, 
        private readonly string $name
    )
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Customer name required.');
        }
    }

    public static function fromName(string $name): self
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Customer name required');
        }

        return new self(
            uniqid('customer_', true),
            $name
        );
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
}
