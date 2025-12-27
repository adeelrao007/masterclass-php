<?php

final class User
{
    private bool $active;

    private function __construct(
        private readonly string $id,
        private Email $email,
        private PasswordHash $passwordHash,
        bool $active
    ) {
        $this->active = $active;
    }

    public static function register(
        string $id,
        Email $email,
        string $plainPassword
    ): self {
        return new self(
            $id,
            $email,
            PasswordHash::fromPlainText($plainPassword),
            true
        );
    }

    public function disable(): void
    {
        if (! $this->active) {
            throw new LogicException('User already disabled');
        }

        $this->active = false;
    }

    public function changeEmail(Email $newEmail): void
    {
        if ($this->email->equals($newEmail)) {
            return;
        }

        $this->email = $newEmail;
    }

    public function changePassword(
        string $currentPassword,
        string $newPassword
    ): void {
        if (! $this->passwordHash->verify($currentPassword)) {
            throw new LogicException('Invalid current password');
        }

        $this->passwordHash = PasswordHash::fromPlainText($newPassword);
    }
}