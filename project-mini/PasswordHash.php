<?php

declare(strict_types=1);

final class PasswordHash
{
    private readonly string $hash;

    private function __construct(string $hash)
    {
        if ($hash === '') {
            throw new InvalidArgumentException('Password hash cannot be empty.');
        }

        $this->hash = $hash;
    }

    /**
     * Create a PasswordHash from a plain-text password
     */
    public static function fromPlainText(
        string $plainPassword,
        int $algo = PASSWORD_DEFAULT,
        array $options = []
    ): self {
        if ($plainPassword === '') {
            throw new InvalidArgumentException('Password cannot be empty.');
        }

        if (strlen($plainPassword) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }

        $hash = password_hash($plainPassword, $algo, $options);

        if ($hash === false) {
            throw new RuntimeException('Password hashing failed.');
        }

        return new self($hash);
    }

    /**
     * Reconstitute from a stored hash (DB → domain)
     */
    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    /**
     * Verify a plain password against the hash
     */
    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hash);
    }

    /**
     * Check if hash needs rehashing (algo/options changed)
     */
    public function needsRehash(
        int $algo = PASSWORD_DEFAULT,
        array $options = []
    ): bool {
        return password_needs_rehash($this->hash, $algo, $options);
    }

    /**
     * Get raw hash value (for persistence only)
     */
    public function value(): string
    {
        return $this->hash;
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}