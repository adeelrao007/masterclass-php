<?php

interface OrderRepository
{
    public function save(Order $order): void;

    public function getById(string $id): Order;

    public function nextIdentity(): string;
}