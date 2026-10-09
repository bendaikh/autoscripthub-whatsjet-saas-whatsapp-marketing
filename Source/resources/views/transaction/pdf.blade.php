<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __tr('Transactions Report') }}</title>
    <style>
        body { font-family: Arial, sans-serif, DejaVu Sans; font-size: 14px; color: #222; }
        h2 { margin-bottom: 8px; }
        .subtitle { margin-bottom: 15px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cfcfcf; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f2f2f2; }
        .lw-text-center { text-align: center;}
    </style>
</head>
<body>
    <h2 class="lw-text-center">{{ __tr('Transactions Report') }}</h2>
    <div class="subtitle lw-text-center">{{ $durationLabel }}</div>
    <table>
        <thead>
            <tr>
                @if(!$isVendor)
                    <th>{{ __tr('Vendor') }}</th>
                @endif
                <th>{{ __tr('Payment Type') }}</th>
                <th>{{ __tr('Payment Method') }}</th>
                <th>{{ __tr('Plan') }}</th>
                <th>{{ __tr('Created At') }}</th>
                <th>{{ __tr('Amount') }}</th>  
                <th>{{ __tr('Reference ID') }}</th>                              
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    @if(!$isVendor)
                        <td>{{ $transaction['vendor_title'] }}</td>
                    @endif
                    <td>{{ $transaction['type'] }}</td>
                    <td>{{ $transaction['payment_method'] }}</td>
                    <td>{{ $transaction['plan'] }}</td>
                    <td>{{ $transaction['created_at'] }}</td>
                    <td>{{ $transaction['amount'] }}</td> 
                    <td>{{ $transaction['reference_id'] }}</td>                                       
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;">{{ __tr('No transactions found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
