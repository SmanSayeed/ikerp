<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            background-color: #fff;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }
        .company-details {
            text-align: right;
        }
        .company-details img {
            width: 150px;
        }
        .table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .total {
            font-weight: bold;
        }
        .table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table width="100%">
            <tr>
                <td>
                    <h2>Company Name</h2>
                    <p>Address Line 1<br>Address Line 2<br>City, Country</p>
                </td>
                <td class="company-details">
                    <img src="{{ asset('images/logo.png') }}" alt="Company Logo">
                </td>
            </tr>
        </table>

        <h2>Invoice</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Node</th>
                    <th>No of Days</th>
                    <th>Unit Price (€)</th>
                    <th>Total (€)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $item['node_name'] }}</td>
                    <td>{{ $item['days_active'] }}</td>
                    <td>€{{ number_format($item['price_per_day'], 2) }}</td>
                    <td>€{{ number_format($item['total'], 2) }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td colspan="3">Grand Total</td>
                    <td>€{{ number_format($totalInvoiceCost, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
