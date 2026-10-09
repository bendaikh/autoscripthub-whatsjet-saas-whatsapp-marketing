<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle ?? __tr('Invoice') }} - {{ $transaction->reference_id ?? $transaction->_uid }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 0; background: #fff; line-height: 1.5; }
        .print-btn-container { text-align: center; margin: 20px 0; }
        .btn-print { padding: 10px 20px; font-size: 14px; background-color: #4e73df; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-print:hover { background-color: #2e59d9; }
        @media print {
            .print-btn-container { display: none; }
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-btn-container">
        <button class="btn-print" onclick="window.print()">
            {{ !empty($isRefund) ? __tr('Print Credit Note') : __tr('Print Invoice') }}
        </button>
    </div>
    @include('transaction.invoice')
</body>
</html>
