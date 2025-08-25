<!DOCTYPE html>
<html>
<head>
    <title>Refund bKash Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Refund Payment</h2>
    <form method="POST" action="{{ route('bkash.refund') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Payment ID</label>
            <input type="text" name="paymentId" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Transaction ID</label>
            <input type="text" name="trxID" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" name="amount" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-danger">Refund</button>
        <a href="{{ route('bkash.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
