@if(isset($isPdf) && $isPdf)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle ?? __tr('Invoice') }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, DejaVu Sans, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 0; background: #fff; line-height: 1.5; }
        .lw-invoice-container { padding: 15px; }
        table { width: 100%; border-collapse: collapse; }
        hr { border: 0; border-top: 1px solid #e5e7eb; margin: 15px 0; }
        .text-right { text-align: right; }
        .lw-brand-block { display: block; }
        .badge-success { background-color: #28a745; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; display: inline-block; font-weight: bold; line-height: 1.2; text-align: center; margin: 0; }
        .badge-warning { background-color: #f6c23e; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 11px; display: inline-block; font-weight: bold; line-height: 1.2; text-align: center; margin: 0; }
        .lw-brand-badge { display: inline-block; margin-top: 5px; white-space: nowrap; float: none; clear: both; }
    </style>
</head>
<body>
@endif

<div class="lw-invoice-container" style="font-family: 'Helvetica Neue', Helvetica, Arial, DejaVu Sans, sans-serif; color: #333; max-width: 800px; margin: 0 auto; padding: 15px; background: #fff; line-height: 1.5;">
    <!-- Header -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="vertical-align: top; padding: 0;">
                <div class="lw-brand-block">
                    @if(!empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" style="max-height: 50px; margin-bottom: 8px; display: block;" />
                    @else
                        <h2 style="margin: 0; color: #4e73df; font-weight: bold; font-size: 24px;">{{ $brandName }}</h2>
                    @endif
                    @if(!empty($isRefund))
                        <span class="badge-warning lw-brand-badge" style="background-color: #f6c23e; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 11px; display: inline-block; font-weight: bold; margin-top: 5px; line-height: 1.2;">{{ __tr('REFUNDED') }}</span>
                    @else
                        <span class="badge-success lw-brand-badge" style="background-color: #28a745; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; display: inline-block; font-weight: bold; margin-top: 5px; line-height: 1.2;">{{ __tr('PAID') }}</span>
                    @endif
                </div>
            </td>
            <td style="text-align: right; vertical-align: top; padding: 0;">
                <h1 style="margin: 0; font-size: 24px; color: #858796; text-transform: uppercase; font-weight: 300;">{{ $documentTitle ?? __tr('Invoice') }}</h1>
                <p style="margin: 5px 0 0 0; font-size: 13px; color: #5a5c69; line-height: 1.4;">
                    <strong>{{ !empty($isRefund) ? __tr('Credit Note No:') : __tr('Invoice No:') }}</strong> {{ $invoiceId }}<br/>
                    @if(!empty($isRefund) && !empty($originalInvoiceId))
                        <strong>{{ __tr('Invoice No:') }}</strong> {{ $originalInvoiceId }}<br/>
                    @endif
                    @if(!empty($isRefund) && !empty($transaction->reference_id))
                        <strong>{{ __tr('Ref ID:') }}</strong> {{ $transaction->reference_id }}<br/>
                    @endif
                    <strong>{{ __tr('Date:') }}</strong> {{ formatDate($transaction->created_at) }}<br/>
                    <strong>{{ __tr('Payment Type:') }}</strong> {{ $paymentMethod }}
                </p>
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px solid #e3e6f0; margin: 15px 0;" />

    <!-- Addresses -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 0 15px 0 0;">
                <h3 style="margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; color: #b7b9cc; letter-spacing: 0.05em;">{{ __tr('From') }}</h3>
                <p style="margin: 0; font-size: 13px; color: #5a5c69; line-height: 1.4;">
                    <strong style="color: #3a3b45;">{{ $brandName }}</strong><br/>
                    {!! nl2br(e($brandAddress)) !!}
                </p>
            </td>
            <td style="width: 50%; vertical-align: top; padding: 0 0 0 15px;">
                <h3 style="margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; color: #b7b9cc; letter-spacing: 0.05em;">{{ __tr('To') }}</h3>
                <p style="margin: 0; font-size: 13px; color: #5a5c69; line-height: 1.4;">
                    <strong style="color: #3a3b45;">{{ $transaction->vendor_title }}</strong><br/>
                    @if(!empty($buyerEmail))
                        {{ $buyerEmail }}<br/>
                    @endif
                </p>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
        <thead>
            <tr style="background: #f8f9fc; border-bottom: 2px solid #e3e6f0;">
                <th style="padding: 10px; text-align: left; font-weight: bold; color: #4e73df; border: 1px solid #e3e6f0;">{{ __tr('Description') }}</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; color: #4e73df; width: 80px; border: 1px solid #e3e6f0;">{{ __tr('Qty') }}</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; color: #4e73df; width: 110px; border: 1px solid #e3e6f0;">{{ __tr('Price') }}</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; color: #4e73df; width: 110px; border: 1px solid #e3e6f0;">{{ __tr('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 12px 10px; border: 1px solid #e3e6f0; line-height: 1.4;">
                    <strong style="color: #3a3b45;">
                        @if(!empty($isRefund))
                            {{ __tr('Refund for') }} {{ $planTitle }} {{ __tr('Subscription') }}
                        @else
                            {{ $planTitle }} {{ __tr('Subscription') }}
                        @endif
                    </strong>
                    @if(!empty($planFrequency))
                        <div style="font-size: 11px; color: #858796; margin-top: 3px;">
                            {{ __tr('Billing Cycle:') }} {{ ucfirst($planFrequency) }}
                        </div>
                    @endif
                    @if(!empty($transaction->notes))
                        <div style="font-size: 11px; color: #858796; margin-top: 3px;">
                            {{ __tr('Notes:') }} {{ $transaction->notes }}
                        </div>
                    @endif
                </td>
                <td style="padding: 12px 10px; text-align: right; border: 1px solid #e3e6f0;">1</td>
                <td style="padding: 12px 10px; text-align: right; border: 1px solid #e3e6f0;">{{ formatAmount($subtotal, true, true) }}</td>
                <td style="padding: 12px 10px; text-align: right; border: 1px solid #e3e6f0;">{{ formatAmount($subtotal, true, true) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Summary -->
    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding: 0 20px 0 0;">
                @if(!empty($additionalInfo))
                    <h4 style="margin: 0 0 6px 0; font-size: 11px; text-transform: uppercase; color: #b7b9cc; letter-spacing: 0.05em;">{{ __tr('Additional Info') }}</h4>
                    <p style="margin: 0; font-size: 11px; color: #858796; white-space: pre-wrap; line-height: 1.4;">{{ $additionalInfo }}</p>
                @endif
            </td>
            <td style="width: 45%; vertical-align: top; padding: 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid #e3e6f0;">
                        <td style="padding: 6px 10px; color: #858796; text-align: right;">{{ __tr('Subtotal') }}</td>
                        <td style="padding: 6px 10px; text-align: right; font-weight: bold; width: 120px; color: #3a3b45;">{{ formatAmount($subtotal, true, true) }}</td>
                    </tr>
                    @if($taxRate > 0)
                        <tr style="border-bottom: 1px solid #e3e6f0;">
                            <td style="padding: 6px 10px; color: #858796; text-align: right;">{{ $taxLabel ?: __tr('Tax') }} ({{ $taxRate }}%)</td>
                            <td style="padding: 6px 10px; text-align: right; font-weight: bold; color: #3a3b45;">{{ formatAmount($taxAmount, true, true) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding: 10px; font-size: 15px; font-weight: bold; color: #3a3b45; text-align: right;">{{ !empty($isRefund) ? __tr('Total Credit') : __tr('Total Paid') }}</td>
                        <td style="padding: 10px; text-align: right; font-size: 16px; font-weight: bold; color: #4e73df;">{{ formatAmount($amount, true, true) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

@if(isset($isPdf) && $isPdf)
</body>
</html>
@endif
