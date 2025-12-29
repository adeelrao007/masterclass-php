System Architecture Overview
┌──────────────────────────────────────────┐
│            Presentation Layer            │
│  (HTTP Controllers / CLI / API Routes)   │
└───────────────────────┬──────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────┐
│            Application Layer             │
│  (Use Cases / Command Handlers)          │
│                                          │
│  - Load Aggregate via Repository         │
│  - Call Domain Behavior                  │
│  - Save Aggregate                        │
│  - Dispatch Domain Events                │
└───────────────────────┬──────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────┐
│               Domain Layer               │
│                                          │
│  Aggregate Roots                         │
│  ────────────────                        │
│  Order, Customer, Invoice                │
│                                          │
│  Entities                                │
│  ─────────                               │
│  Order, Payment                          │
│                                          │
│  Value Objects                           │
│  ─────────────                           │
│  OrderId, Money, Email, OrderStatus      │
│                                          │
│  Domain Services / Policies              │
│  ──────────────────────                  │
│  RefundPolicy, OrderCompletionPolicy     │
│                                          │
│  Domain Events                           │
│  ─────────────                           │
│  OrderPaid, InvoiceIssued                │
└───────────────────────┬──────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────┐
│           Infrastructure Layer           │
│                                          │
│  - Eloquent Models                       │
│  - Repository Implementations            │
│  - Event Listeners                       │
│  - External Services (Email, Payments)   │
└──────────────────────────────────────────┘

2️⃣ Laravel Folder Structure (Clean Architecture)
This structure works in real Laravel apps without fighting the framework.

app/
├── Domain/
│   ├── Order/
│   │   ├── Order.php                 # Aggregate Root
│   │   ├── OrderStatus.php            # Value Object
│   │   ├── OrderId.php                # Value Object
│   │   ├── Events/
│   │   │   └── OrderPaid.php
│   │   ├── Policies/
│   │   │   └── OrderCompletionPolicy.php
│   │   └── Repositories/
│   │       └── OrderRepository.php    # Interface
│   │
│   ├── Customer/
│   │   └── Customer.php
│   │
│   ├── Invoice/
│   │   └── Invoice.php
│
├── Application/
│   ├── Order/
│   │   ├── PayOrder/
│   │   │   ├── PayOrderCommand.php
│   │   │   └── PayOrderHandler.php
│
├── Infrastructure/
│   ├── Persistence/
│   │   └── Eloquent/
│   │       ├── Models/
│   │       │   └── OrderModel.php
│   │       └── Repositories/
│   │           └── EloquentOrderRepository.php
│   │
│   ├── Events/
│   │   └── Listeners/
│   │       ├── GenerateInvoiceOnOrderPaid.php
│   │       └── SendOrderReceiptEmail.php
│
├── Http/
│   └── Controllers/
│       └── PayOrderController.php

🔑 Key Rules
Domain has no Laravel imports
Repositories interfaces live in Domain
Eloquent stays in Infrastructure
Application layer orchestrates only

3️⃣ Full Request → Domain → Event → Listener Flow
Step 1 — HTTP Request
POST /orders/{id}/pay

final class PayOrderController
{
    public function __invoke(string $orderId, Request $request)
    {
        $this->handler->handle(
            new PayOrderCommand(
                OrderId::fromString($orderId),
                PaymentId::fromString($request->payment_id)
            )
        );

        return response()->json(['status' => 'ok']);
    }
}

Step 2 — Application Layer (Use Case)
final class PayOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private EventDispatcher $events
    ) {}

    public function handle(PayOrderCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $order = $this->orders->byId($command->orderId);

            $order->pay($command->paymentId);

            $this->orders->save($order);
        });

        $this->events->dispatchAll(
            $order->pullEvents()
        );
    }
}
✔ Transaction here
✔ Domain untouched
✔ Events dispatched after commit

Step 3 — Domain Aggregate Emits Event
final class Order
{
    public function pay(PaymentId $paymentId): void
    {
        $this->status = $this->status->pay();

        $this->recordEvent(
            new OrderPaid($this->id, $paymentId)
        );
    }
}
✔ Entity makes decision
✔ Event records fact

Step 4 — Event Listener Reacts
final class GenerateInvoiceOnOrderPaid
{
    public function handle(OrderPaid $event): void
    {
        $invoice = Invoice::createForOrder($event->orderId);

        $this->invoices->save($invoice);
    }
}
✔ Separate transaction
✔ Can retry
✔ No coupling

🧩 Architecture Overview
This project follows Domain-Driven Design (DDD) and Clean Architecture principles.

Core Concepts

Entities
Objects with identity and lifecycle (Order, Customer).
They protect business invariants and contain behavior.

Value Objects
Immutable, identity-less objects (Money, Email, OrderStatus).
They validate and encapsulate domain rules.

Aggregates
Consistency boundaries.
Only the Aggregate Root may be accessed externally.

Repositories
Abstract persistence behind domain interfaces.
The domain does not know how data is stored.

Domain Events
Facts about something that already happened.
Used to decouple aggregates and enable eventual consistency.

Application Services
Orchestrate use cases.
They load aggregates, call domain behavior, persist changes, and dispatch events.

Why This Architecture?
Prevents anemic models
Avoids ORM-driven design
Enables independent scaling
Keeps business rules framework-agnostic
Improves long-term maintainability

One Golden Rule
Entities decide.
Value Objects validate.
Repositories persist.
Events notify.
Application services orchestrate.