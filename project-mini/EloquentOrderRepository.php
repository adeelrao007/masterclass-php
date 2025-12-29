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
        return new Order(
            new OrderId($orderModel->id),
            // ...other fields...
            new OrderStatus($orderModel->status)
        );
    }
}