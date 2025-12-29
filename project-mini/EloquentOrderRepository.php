<?php

class EloquentOrderRepository implements OrderRepository
{
    public function byId(OrderId $orderId): ?Order
    {
        $orderModel = OrderModel::where('id', $orderId->toString())->first();
        if (!$orderModel) {
            return null;
        }
        // Reconstitute domain Order from persistence
        return $this->reconstitute($orderModel);
    }

    public function save(Order $order): void
    {
        $orderModel = OrderModel::updateOrCreate(
            ['id' => $order->id()->toString()],
            [
                'status' => $order->status()->value(),
                // ...other fields...
            ]
        );
        // Optionally handle related entities/VOs
    }

    private function reconstitute(OrderModel $orderModel): Order
    {
        return Order::reconstitute(
            new OrderId($orderModel->id),
            'customer', // assuming customerId is stored elsewhere
            0, // assuming total is stored elsewhere
            new OrderStatus($orderModel->status),
            // other persisted fields
        );
    }

    public function nextIdentity(): OrderId
    {
        return OrderId::generate();
    }

    public function getById(string $id): Order
    {
        $orderModel = OrderModel::where('id', $id)->first();
        if (!$orderModel) {
            throw new RuntimeException("Order not found: " . $id);
        }
        return $this->reconstitute($orderModel);
    }
}