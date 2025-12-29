<?php

class EloquentOrderRepository implements OrderRepository
{
    public function nextIdentity(): OrderId
    {
        return OrderId::generate();
    }

    public function byId(OrderId $orderId): ?Order
    {
        $model = OrderModel::find($orderId->toString());

        return $model ? $this->reconstitute($model) : null;
    }

    public function save(Order $order): void
    {
        $model = OrderModel::find($order->id()->toString())
            ?? new OrderModel(['id' => $order->id()->toString()]);

        $model->status = $order->status()->value();
        $model->save();
    }

    private function reconstitute(OrderModel $model): Order
    {
        return Order::reconstitute(
            new OrderId($model->id),
            new OrderStatus($model->status)
        );
    }
}