## bKash Payment Flow

### 1. Payment Modes
bKash provides three dynamic charging modes:

| Mode                  | Value | Description                                                      |
|-----------------------|-------|------------------------------------------------------------------|
| Agreement Only        | 1000  | For recurring payments where you have customer’s agreement. No immediate payment. |
| Payment with Agreement| 1001  | Uses saved agreement and executes a payment immediately.        |
| Payment Only          | 1011  | Single payment without agreement. Customer provides their number at checkout. |

---

### 2. Steps to Collect Payment

**Create Payment**  
Your server sends a request to bKash API `/payment/create` including:

- Amount, currency, intent (sale for immediate payment)
- Payment mode (1011 for one-time)
- Merchant invoice number (unique identifier)
- Callback URLs

**Response:** bKash sends back:

- `paymentId` → unique transaction ID
- `bkashURL` → link to redirect the customer to bKash checkout
- Status: usually `Initiated`

**Redirect Customer**
- Customer visits `bkashURL`
- Logs in with bKash credentials
- Confirms payment

**Customer Action**

- **Success** → payment completed
- **Failure** → payment failed
- **Cancel** → customer aborted the payment

**Callback URL**
- A server endpoint in your system
- bKash uses it to notify your system about the payment result
- Can be used to finalize the payment, update your database, and notify the customer

**Execute Payment (if success)**
- Your server calls `/payment/execute` using `paymentId` to confirm the transaction
- bKash responds with final status and transaction details

---

### 3. Callback URLs Explained

| Type                   | Purpose                                                                 |
|------------------------|-------------------------------------------------------------------------|
| `callbackURL`          | Main backend URL where bKash sends payment info. For server-to-server notifications. |
| `successCallbackURL`   | Redirect customer if payment succeeds. Shows “Payment Success” page.    |
| `failureCallbackURL`   | Redirect customer if payment fails. Shows “Payment Failed” page.        |
| `cancelledCallbackURL` | Redirect if customer cancels the payment. Shows “Payment Cancelled” page. |

**Key Points:**

- `callbackURL` is for your server to receive updates (must be publicly accessible)
- `success/failure/cancelled` URLs are mainly for user experience
- These URLs allow your system to handle all outcomes systematically

---

### 4. Important Concepts

- **Payment ID (`paymentId`)**: Unique identifier for a transaction
- **Merchant Invoice Number**: Your internal unique order ID
- **Intent**: Usually `sale` for immediate payment
- **Transaction Status:**
    - `Initiated` → created but not executed
    - `Completed` → successfully executed
    - `Failed/Cancelled` → unsuccessful

---

### 5. Summary

1. Create payment → get `paymentId` and `bkashURL`
2. Redirect customer to bKash checkout
3. Customer completes, cancels, or fails payment
4. bKash calls your `callbackURL` (server) and redirects to `success/failure/cancelled` URLs (customer browser)
5. Execute payment on success → finalize transaction
