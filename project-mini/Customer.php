<?php

final class Customer
{

    public function __construct(private readonly string $id, private readonly string $name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Customer name required.');
        }
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
}
