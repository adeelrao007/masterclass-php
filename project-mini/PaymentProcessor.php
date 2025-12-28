<?php

final class PaymentProcessor
{
    public function process(
        Invoice $invoice,
        Payment $payment
    ): void {
        if (! $payment->matchesInvoice($invoice)) {
            throw new LogicException('Invalid payment');
        }

        $invoice->markPaid();
        $payment->markProcessed($invoice->getAmount());
    }
}
