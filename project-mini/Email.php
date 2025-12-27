<?php

declare(strict_types=1);

final class Email
{
    private readonly string $value;

    public function __construct(string $email)
    {
        $email = trim($email);

        if ($email === '') {
            throw new InvalidArgumentException('Email cannot be empty.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }

        $this->value = strtolower($email);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}