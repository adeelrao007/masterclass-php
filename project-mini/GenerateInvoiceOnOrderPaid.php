<?php

final class GenerateInvoiceOnOrderPaid
{
    public function handle(OrderPaid $event): void
    {
        $invoice = Invoice::createForOrder(
            $event->orderId,
            $event->paymentId
        );

        $invoice->save($invoice);
    }
}