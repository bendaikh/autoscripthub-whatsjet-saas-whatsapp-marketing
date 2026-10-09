@php
/**
* Component : Transaction
* Controller : TransactionController
* File : transaction.list.blade.php
* ----------------------------------------------------------------------------- */
@endphp
@extends('layouts.app', ['title' => $pageTitle ?? __tr('Transactions')])
@section('content')
@include('users.partials.header', [
'title' => $pageTitle ?? __tr('Transactions'),
'description' => '',
'class' => 'col-lg-7'
])

@php
    $isVendor = $isVendor ?? false;
    $pageTitle = $isVendor ? __tr('My Transactions') : __tr('Transactions');
    $listViewRoute = $isVendor ? 'vendor.transaction.read.list_view' : 'central.transaction.read.list_view';
    $listDataRoute = $isVendor ? 'vendor.transaction.read.list' : 'central.transaction.read.list';
    $downloadPdfRoute = $isVendor ? 'vendor.transaction.read.download_process' : 'central.transaction.read.download_process';
    $invoiceDetailsRoute = $isVendor ? 'vendor.transaction.read.invoice_details' : 'central.transaction.read.invoice_details';
    $printInvoiceRoute = $isVendor ? 'vendor.transaction.read.print_invoice' : 'central.transaction.read.print_invoice';
    $downloadInvoiceRoute = $isVendor ? 'vendor.transaction.read.download_invoice' : 'central.transaction.read.download_invoice';

    $requestDuration = !__isEmpty(request()->duration) ? request()->duration : '';
    $startDate = request()->trx_start_date;
    $endDate = request()->trx_end_date;
    $paymentType = request()->payment_type;
    $isExtendedLicence = (getAppSettings('product_registration', 'licence') === 'dee257a8c3a2656b7d7fbe9a91dd8c7c41d90dc9');

    $listDataUrlParams = [
        'paymentType' => $paymentType
    ];
    $downloadPdfUrlParams = [
        'paymentType' => $paymentType
    ];
    if (!empty($startDate) && !empty($endDate)) {
        $listDataUrlParams['startDate'] = $startDate;
        $listDataUrlParams['endDate'] = $endDate;
        $downloadPdfUrlParams['startDate'] = $startDate;
        $downloadPdfUrlParams['endDate'] = $endDate;
    }
@endphp

<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body p-4">
            <form  id="lwMessageFilterForm" action="{{ route($listViewRoute) }}" method="get" data-show-processing="true">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <x-lw.input-field type="select" id="lwTransactionDurationSelect" data-form-group-class=""
                            name="duration" data-selected=""
                            :label="__tr('Duration')" placeholder="{{ __tr('Duration') }}" >
                            <x-slot name="selectOptions">
                                <option value="" selected disabled>{{ __tr('Select Duration') }}</option>
                                @foreach(configItem('durations') as $durationKey => $duration)
                                    <option value="{{ $durationKey }}" @if($requestDuration == $durationKey) selected @endif>{{ $duration }}</option>
                                @endforeach
                            </x-slot>
                        </x-lw.input-field>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-3">                        
                        <x-lw.input-field type="date" id="lwStartData" data-form-group-class=""
                            :label="__tr('Start Date')" name="trx_start_date" value="{{ $startDate }}" max="{{ now()->format('Y-m-d') }}"/>

                        @error('trx_start_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <x-lw.input-field type="date" id="lwEndDate" data-form-group-class="" value="{{ $endDate }}"
                            :label="__tr('End Date')" name="trx_end_date" max="{{ now()->format('Y-m-d') }}"/>

                        @error('trx_end_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <x-lw.input-field type="select" id="lwPaymentTypeSelect" data-form-group-class=""
                            name="payment_type"
                            :label="__tr('Payment Type')" placeholder="{{ __tr('Payment Type') }}" >
                            <x-slot name="selectOptions">
                                <option value="all" @if(empty($paymentType) || strtolower($paymentType) == 'all') selected @endif>{{ __tr('All') }}</option>
                                <option value="Purchase" @if(strtolower($paymentType ?? '') == 'purchase') selected @endif>{{ __tr('Purchase') }}</option>
                                <option value="Refund" @if(strtolower($paymentType ?? '') == 'refund') selected @endif>{{ __tr('Refund') }}</option>
                            </x-slot>
                        </x-lw.input-field>
                    </div>
                    @if($isExtendedLicence)
                        <div class="col-sm-12 col-md-12 col-lg-12 text-right mt-3">
                            <button type="submit" class="btn btn-primary">{{ __tr('Show') }}</button>
                            <button id="lwClearTransactionFilterBtn" type="button" class="btn btn-secondary">{{ __tr('Clear Filter') }}</button>
                            <button id="leDownloadTrxPdfBtn" type="button" class="btn btn-primary" disabled><i class="fas fa-download"></i> {{ __tr('Download Pdf') }}</button>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            @if($isExtendedLicence)
                <x-lw.datatable data-page-length="100" id="lwTransactionsList"
                    :url="route($listDataRoute, $listDataUrlParams)" data-callback="transactionDataLoaded">
                    @if(!$isVendor)
                    <th data-template="#transactionVendorColumnTemplate" data-name="null">{{ __tr('Vendor') }}</th>
                    @endif
                    <th data-orderable="true" data-template="#transactionTypeColumnTemplate" data-name="formatted_type">{{ __tr('Payment Type') }}</th>
                    <th data-orderable="false" data-name="payment_method">{{ __tr('Payment Method') }}</th>
                    <th data-orderable="false" data-name="plan">{{ __tr('Plan') }}</th>
                    <th data-order-by="true" data-order-type="desc" data-orderable="true" data-name="created_at">{{ __tr('Created At') }}</th>
                    <th data-orderable="true" data-name="amount">{{ __tr('Amount') }}</th>
                    <th data-orderable="true" data-name="reference_id">{{ __tr('Reference ID') }}</th>
                    <th data-template="#transactionActionColumnTemplate" name="null">{{ __tr('Action') }}</th>
                </x-lw.datatable>
            @else
                <div class="alert alert-warning my-3">
                    <strong title="Extended Licence Required"><?= __tr('Extended Licence Required') ?></strong> <br>
                    <?= __tr('Extended licence is required to view, print and download transaction.') ?>
                </div>
            @endif

            <!-- vendor column template -->
            <script type="text/template" id="transactionVendorColumnTemplate">
                <% if(__tData.vendor_uid) { %>
                    <a href="<%= __Utils.apiURL("{{ route('vendor.dashboard',['vendorIdOrUid'=>'vendorIdOrUid'])}}", {'vendorIdOrUid':__tData.vendor_uid}) %>"> <%-__tData.vendor_title %> </a>
                <% } else { %>
                    -
                <% } %>
            </script>

            <script type="text/template" id="transactionTypeColumnTemplate">
                <% if(__tData.type == 'PURCHASE') { %>
                    <span class="badge badge-success"><%- __tData.formatted_type %></span>
                <% } else if(__tData.type == 'REFUND') { %>
                    <span class="badge badge-warning"><%- __tData.formatted_type %></span>
                <% } %>
                <span class="badge badge-pill badge-secondary"><%- __tData.formatted_refund_type %></span>                
            </script>

            <!-- action template -->
            <script type="text/template" id="transactionActionColumnTemplate">
                <% var isRefund = (__tData.type == 'REFUND'); %>
                <!-- View Details Modal Trigger -->
                <a data-pre-callback="appFuncs.clearContainer" title="<%= isRefund ? '{{ __tr('View Credit Note') }}' : '{{ __tr('View Details') }}' %>" class="btn btn-primary btn-sm lw-ajax-link-action" data-response-template="#lwInvoiceDetailsBody" href="<%= __Utils.apiURL("{{ route($invoiceDetailsRoute, ['transactionIdOrUid']) }}", {'transactionIdOrUid': __tData._uid}) %>" data-toggle="modal" data-target="#lwInvoiceDetailsModal"><i class="fa fa-eye"></i> {{ __tr('View') }}</a>
                
                <!-- Print Invoice / Credit Note -->
                <a href="<%= __Utils.apiURL("{{ route($printInvoiceRoute, ['transactionIdOrUid']) }}", {'transactionIdOrUid': __tData._uid}) %>" target="_blank" class="btn btn-secondary btn-sm" title="<%= isRefund ? '{{ __tr('Print Credit Note') }}' : '{{ __tr('Print Invoice') }}' %>"><i class="fa fa-print"></i> {{ __tr('Print') }}</a>
                
                @if($isVendor and getAppSettings('allow_vendor_to_download_invoice') or hasCentralAccess())
                    <!-- Download Invoice / Credit Note -->
                    <a href="<%= __Utils.apiURL("{{ route($downloadInvoiceRoute, ['transactionIdOrUid']) }}", {'transactionIdOrUid': __tData._uid}) %>" class="btn btn-success btn-sm" title="<%= isRefund ? '{{ __tr('Download Credit Note') }}' : '{{ __tr('Download Invoice') }}' %>"><i class="fa fa-download"></i> {{ __tr('Download') }}</a>
                @endif

                @if(hasCentralAccess())
                    <% if(!isRefund && __tData.refund_type != 'FULL_REFUND') { %>
                        <a data-pre-callback="appFuncs.clearContainer" title="{{ __tr('Refund') }}" class="btn btn-warning btn-sm lw-ajax-link-action" data-response-template="#lwPaymentRefundBody" href="<%= __Utils.apiURL("{{ route('central.transaction.read.refund_details', ['transactionIdOrUid']) }}", {'transactionIdOrUid': __tData._uid}) %>" data-toggle="modal" data-target="#lwPaymentRefundModal"><i class="fas fa-money-bill-wave"></i> {{ __tr('Refund') }}</a>
                    <% } %>
                @endif
            </script>
            <!-- /action template -->

            <!-- Invoice Details Modal -->
            <x-lw.modal id="lwInvoiceDetailsModal" modal-dialog-class="modal-lg" :header="__tr('Invoice / Credit Note Details')">
                <div id="lwInvoiceDetailsBody" class="lw-form-modal-body"></div>
                <script type="text/template" id="lwInvoiceDetailsBody-template">
                    <%= __tData.invoiceHtml %>
                </script>
            </x-lw.modal>
            <!-- /Invoice Details Modal -->

            <!-- Transaction delete template -->
            <script type="text/template" id="lwDeleteTransaction-template">
                <h2>{{ __tr('Are You Sure!') }}</h2>
                <p>{{ __tr('You want to delete this Transaction?') }}</p>
            </script>
            <!-- /Transaction delete template -->
        </div>
    </div>
</div>
@include('transaction.refund')
@endsection()

@push('appScripts')
<script>
    $(document).on('change', '#lwTransactionDurationSelect', function () {
        const duration = $(this).val();
        const today = new Date();

        let startDate = null;
        let endDate = null;

        switch (duration) {
            case 'today':
                startDate = endDate = new Date(today);
                break;

            case 'yesterday':
                startDate = endDate = new Date(today);
                startDate.setDate(startDate.getDate() - 1);
                endDate = new Date(startDate);
                break;

            case 'current_week':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - today.getDay() + 1); // Monday
                endDate = new Date(today);
                break;

            case 'last_week':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - today.getDay() - 6); // Last Monday

                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);
                break;

            case 'current_month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today);
                break;

            case 'last_month':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;

            case 'current_year':
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today);
                break;

            case 'last_year':
                startDate = new Date(today.getFullYear() - 1, 0, 1);
                endDate = new Date(today.getFullYear() - 1, 11, 31);
                break;

            case 'last_30_days':
                endDate = new Date(today);
                startDate = new Date(today);
                startDate.setDate(today.getDate() - 29); // Includes today
                break;

            case 'custom':
                $('#lwStartData').val('');
                $('#lwEndDate').val('');
                return;
        }

        $('#lwStartData').val(formatDate(startDate));
        $('#lwEndDate').val(formatDate(endDate));
    });

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function hasActiveTransactionFilters() {
        const duration = $('#lwTransactionDurationSelect').val();
        const startDate = $('#lwStartData').val();
        const endDate = $('#lwEndDate').val();
        const paymentType = $('#lwPaymentTypeSelect').val();

        return !!(duration || startDate || endDate || (paymentType && paymentType !== 'all' && paymentType !== ''));
    }

    window.transactionDataLoaded = function(responseData) {
        $('#leDownloadTrxPdfBtn').prop('disabled', responseData.json.data.length == 0);
    }

    $('#lwClearTransactionFilterBtn').on('click', function () {
        window.location.href = "{{ route($listViewRoute) }}";
    });

    $('#leDownloadTrxPdfBtn').on('click', function () {
        window.location.href = "{{ route($downloadPdfRoute, $downloadPdfUrlParams) }}";
    });
    $('#lwClearTransactionFilterBtn').prop('disabled', !hasActiveTransactionFilters());
</script>
@endpush
