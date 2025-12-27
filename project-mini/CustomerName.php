<?php

final class CustomerName
{
    public function __construct(private readonly string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('Customer name required');
        }
    }
}