<?php

final class Customer
{

    public function __construct(private readonly string $id, private readonly string $name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Customer name required.');
        }
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
}
