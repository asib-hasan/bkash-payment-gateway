<!DOCTYPE html>
<html>
<head>
    <title>bKash Payment Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">bKash Payment Demo</h2>
    <a href="{{ route('bkash.create') }}" class="btn btn-primary">Create Payment</a>
    <a href="{{ url('bkash/refund') }}" class="btn btn-danger">Refund Payment</a>
</div>
</body>
</html>
