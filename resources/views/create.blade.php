<!DOCTYPE html>
<html>
<head>
    <title>Create bKash Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Create Payment</h2>
    <form method="POST" action="{{ route('bkash.checkout') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" name="amount" class="form-control" value="100" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Payer Reference</label>
            <input type="text" name="payer" class="form-control" value="017XXXXXXXX" required>
        </div>
        <button type="submit" class="btn btn-success">Pay with bKash</button>
        <a href="{{ route('bkash.create') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
