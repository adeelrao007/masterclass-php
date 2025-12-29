System Architecture Overview<br>
┌──────────────────────────────────────────┐<br>
│            Presentation Layer            │<br>
│  (HTTP Controllers / CLI / API Routes)   │<br>
└───────────────────────┬──────────────────┘<br>
                        │<br>
                        ▼<br>
┌──────────────────────────────────────────┐<br>
│            Application Layer             │<br>
│  (Use Cases / Command Handlers)          │<br>
│                                          │<br>
│  - Load Aggregate via Repository         │<br>
│  - Call Domain Behavior                  │<br>
│  - Save Aggregate                        │<br>
│  - Dispatch Domain Events                │<br>
└───────────────────────┬──────────────────┘<br>
                        │<br>
                        ▼<br>
┌──────────────────────────────────────────┐<br>
│               Domain Layer               │<br>
│                                          │<br>
│  Aggregate Roots                         │<br>
│  ────────────────                        │<br>
│  Order, Customer, Invoice                │<br>
│                                          │<br>
│  Entities                                │<br>
│  ─────────                               │<br>
│  Order, Payment                          │<br>
│                                          │<br>
│  Value Objects                           │<br>
│  ─────────────                           │<br>
│  OrderId, Money, Email, OrderStatus      │<br>
│                                          │<br>
│  Domain Services / Policies              │<br>
│  ──────────────────────                  │<br>
│  RefundPolicy, OrderCompletionPolicy     │<br>
│                                          │<br>
│  Domain Events                           │<br>
│  ─────────────                           │<br>
│  OrderPaid, InvoiceIssued                │<br>
└───────────────────────┬──────────────────┘<br>
                        │<br>
                        ▼<br>
┌──────────────────────────────────────────┐<br>
│           Infrastructure Layer           │<br>
│                                          │<br>
│  - Eloquent Models                       │<br>
│  - Repository Implementations            │<br>
│  - Event Listeners                       │<br>
│  - External Services (Email, Payments)   │<br>
└──────────────────────────────────────────┘<br>
<br>
2️⃣ Laravel Folder Structure (Clean Architecture)<br>
This structure works in real Laravel apps without fighting the framework.<br>
<br>
app/<br>
├── Domain/<br>
│   ├── Order/<br>
│   │   ├── Order.php                 # Aggregate Root<br>
│   │   ├── OrderStatus.php            # Value Object<br>
│   │   ├── OrderId.php                # Value Object<br>
│   │   ├── Events/<br>
│   │   │   └── OrderPaid.php<br>
│   │   ├── Policies/<br>
│   │   │   └── OrderCompletionPolicy.php<br>
│   │   └── Repositories/<br>
│   │       └── OrderRepository.php    # Interface<br>
│   │<br>
│   ├── Customer/<br>
│   │   └── Customer.php<br>
│   │<br>
│   ├── Invoice/<br>
│   │   └── Invoice.php<br>
│<br>
├── Application/<br>
│   ├── Order/<br>
│   │   ├── PayOrder/<br>
│   │   │   ├── PayOrderCommand.php<br>
│   │   │   └── PayOrderHandler.php<br>
│<br>
├── Infrastructure/<br>
│   ├── Persistence/<br>
│   │   └── Eloquent/<br>
│   │       ├── Models/<br>
│   │       │   └── OrderModel.php<br>
│   │       └── Repositories/<br>
│   │           └── EloquentOrderRepository.php<br>
│   │<br>
│   ├── Events/<br>
│   │   └── Listeners/<br>
│   │       ├── GenerateInvoiceOnOrderPaid.php<br>
│   │       └── SendOrderReceiptEmail.php<br>
│<br>
├── Http/<br>
│   └── Controllers/<br>
│       └── PayOrderController.php<br>
<br>
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