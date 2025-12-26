<?php

final class User
{

    public function __construct(private readonly string $id, private readonly string $email, private readonly string $passwordHash, private readonly bool $active = true)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }
        if (empty($passwordHash)) {
            throw new InvalidArgumentException('Password hash required.');
        }
    }

    public function getId(): string { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function isActive(): bool { return $this->active; }

    public function disable(): void { $this->active = false; }
    public function enable(): void { $this->active = true; }
}
