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
        <!-- Client Information -->
        <table width="100%">
            <tr>
                <td>
                    <h3>{{ $client['name'] }}</h3>
                    @if($client['address'])
                        <p>{{ $client['address'] }}</p>
                    @else
                        <p>No Address Provided</p>
                    @endif
                </td>
                <!-- Shop Information -->
                <td class="company-details">
                    <img src="{{ asset('images/logo.png') }}" alt="Company Logo">
                    <h3>Shop Name</h3>
                    <p>
                        Address: Laan van Zuid Hoorn 60, 2289DE Rijswijk<br>
                        VAT Number (BTW nr.): NL123456789B01<br>
                        Chamber of Commerce Number (KvK nr.): 123456789<br>
                        IBAN: NL21ABNA0532484010
                    </p>
                </td>
            </tr>
        </table>

        <!-- Invoice Details -->
        <h3>Invoice Details</h3>
        <p>
            Invoice Number (Factuurnummer): {{ $invoice_id }}<br>
            Invoice Date (Factuurdatum): {{ $invoice_date }}<br>
            Due Date (Vervaldatum): {{ \Carbon\Carbon::now()->addDays(14)->format('d-m-Y') }}
        </p>

        <!-- Invoice Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>Node</th>
                    <th>No of Days</th>
                    <th>Unit Price (€)</th>
                    <th>Original Total (€)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item['node_name'] }}</td>
                        <td>{{ $item['days_active'] }}</td>
                        <td>€{{ number_format($item['price_per_day'], 2) }}</td>
                        <td>€{{ number_format($item['original_total'], 2) }}</td>

                    </tr>
                @endforeach
                <tr class="total">
                    <td colspan="3"> Total</td>
                    <td>€{{ number_format($originalInvoiceCost, 2) }}</td>
                </tr>
                <tr class="total">
                    <td colspan="3"> Discount({{$vip_discount ?? 0}}%)</td>
                    <td>€{{ number_format($discount, 2) }}</td>
                </tr>
                <tr class="total">
                    {{-- @if($totalInvoiceCost < $originalInvoiceCost) --}}
                        <td colspan="3">Grand Total</td>
                        <td>€{{ number_format($totalInvoiceCost, 2) }}</td>
                    {{-- @endif --}}
                </tr>


            </tbody>
        </table>
    </div>
</body>
</html>
