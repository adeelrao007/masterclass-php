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
🔑 Key Rules<br>
Domain has no Laravel imports<br>
Repositories interfaces live in Domain<br>
Eloquent stays in Infrastructure<br>
Application layer orchestrates only<br>
<br>
3️⃣ Full Request → Domain → Event → Listener Flow<br>
Step 1 — HTTP Request<br>
POST /orders/{id}/pay<br>
<br>
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
<br>
Step 2 — Application Layer (Use Case)<br>
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
}<br>
✔ Transaction here<br>
✔ Domain untouched<br>
✔ Events dispatched after commit<br>
<br>
Step 3 — Domain Aggregate Emits Event<br>
final class Order
{
    public function pay(PaymentId $paymentId): void
    {
        $this->status = $this->status->pay();

        $this->recordEvent(
            new OrderPaid($this->id, $paymentId)
        );
    }
}<br>
✔ Entity makes decision<br>
✔ Event records fact<br>
<br>
Step 4 — Event Listener Reacts<br>
final class GenerateInvoiceOnOrderPaid
{
    public function handle(OrderPaid $event): void
    {
        $invoice = Invoice::createForOrder($event->orderId);

        $this->invoices->save($invoice);
    }
}<br>
✔ Separate transaction<br>
✔ Can retry<br>
✔ No coupling<br>
<br>
🧩 Architecture Overview<br>
This project follows Domain-Driven Design (DDD) and Clean Architecture principles.<br>
<br>
Core Concepts<br>
Entities<br>
Objects with identity and lifecycle (Order, Customer).<br>
They protect business invariants and contain behavior.<br>
<br>
Value Objects<br>
Immutable, identity-less objects (Money, Email, OrderStatus).<br>
They validate and encapsulate domain rules.<br>
<br>
Aggregates<br>
Consistency boundaries.<br>
Only the Aggregate Root may be accessed externally.<br>
<br>
Repositories<br>
Abstract persistence behind domain interfaces.<br>
The domain does not know how data is stored.<br>
<br>
Domain Events<br>
Facts about something that already happened.<br>
Used to decouple aggregates and enable eventual consistency.<br>
<br>
Application Services<br>
Orchestrate use cases.<br>
They load aggregates, call domain behavior, persist changes, and dispatch events.<br>
<br>
Why This Architecture?<br>
Prevents anemic models<br>
Avoids ORM-driven design<br>
Enables independent scaling<br>
Keeps business rules framework-agnostic<br>
Improves long-term maintainability<br>
<br>
One Golden Rule<br>
Entities decide.<br>
Value Objects validate.<br>
Repositories persist.<br>
Events notify.<br>
Application services orchestrate.<br>