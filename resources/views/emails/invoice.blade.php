<!DOCTYPE html>
<html>
<head>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 10px;}
        th, td { border: 1px solid #ddd; padding: 8px;}
        th { background-color: #f2f2f2;}
    </style>
</head>
<body>
<h2>Invoice - {{ $date }}</h2>
<p>Dear {{ $email }},</p>
<p>Here is the summary of your payments for today:</p>
<table>
    <thead>
    <tr>
        <th>Date</th>
        <th>Reference</th>
        <th>Original Amount</th>
        <th>Currency</th>
        <th>USD Equivalent</th>
    </tr>
    </thead>
    <tbody>
    @php $total = 0; @endphp
    @foreach($payments as $payment)
        <tr>
            <td>{{ $payment->payment_date }}</td>
            <td>{{ $payment->reference_number }}</td>
            <td>{{ $payment->original_amount }}</td>
            <td>{{ $payment->original_currency }}</td>
            <td>${{ number_format($payment->usd_amount, 2) }}</td>
        </tr>
        @php $total += $payment->usd_amount; @endphp
    @endforeach
    </tbody>
</table>
<p><strong>Total: ${{ number_format($total, 2) }}</strong></p>
<p>Thank you for your business.</p>
</body>
</html>
