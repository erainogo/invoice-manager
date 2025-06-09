<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h2 {
            color: #2c3e50;
        }
        .container {
            background: #fff;
            padding: 25px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 10px 12px;
            text-align: left;
        }
        th {
            background-color: #f4f6f8;
            color: #34495e;
        }
        tfoot td {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        p {
            line-height: 1.6;
        }
        .total {
            text-align: right;
            margin-top: 15px;
            font-size: 1.1em;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
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
        <tfoot>
        <tr>
            <td colspan="4" style="text-align: right;">Total:</td>
            <td>${{ number_format($total, 2) }}</td>
        </tr>
        </tfoot>
    </table>

    <p>Thank you for your business.</p>
</div>
</body>
</html>
