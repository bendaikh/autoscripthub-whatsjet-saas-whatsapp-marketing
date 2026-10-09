<?php
/**
* VendorTransactionController.php - Controller file for vendor transactions
*
* This file is part of the Transaction component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Transaction\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Base\BaseRequestTwo;
use App\Yantrana\Components\Transaction\TransactionEngine;

class VendorTransactionController extends BaseController
{
    /**
     * @var TransactionEngine $transactionEngine - Transaction Engine
     */
    protected $transactionEngine;

    /**
     * Constructor
     *
     * @param TransactionEngine $transactionEngine - Transaction Engine
     *
     * @return void
     *-----------------------------------------------------------------------*/

    function __construct(TransactionEngine $transactionEngine)
    {
        $this->transactionEngine = $transactionEngine;
    }

    /**
     * Show vendor transactions list view
     *
     * @param BaseRequestTwo $request
     * @return View
     *---------------------------------------------------------------- */
    public function showTransactionListView(BaseRequestTwo $request)
    {
        if (!__isEmpty($request->input('duration'))) {
            $request->validate([
                'trx_start_date' => 'required|date|before_or_equal:trx_end_date',
                'trx_end_date' => 'required|date|after_or_equal:trx_start_date'
            ]);
        }

        return $this->loadView('transaction.list', [
            'isVendor' => true,
        ]);
    }

    /**
     * Prepare vendor transactions DataTable data
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return json
     *---------------------------------------------------------------- */
    public function prepareTransactionList($paymentType = 'all', $startDate = null, $endDate = null)
    {
        return $this->transactionEngine->prepareTransactionDataTableSource($startDate, $endDate, getVendorId(), $paymentType);
    }

    /**
     * Process vendor transactions pdf download
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *---------------------------------------------------------------- */
    public function processDownloadTransactionPdf($paymentType = 'all', $startDate = null, $endDate = null)
    {
        return $this->transactionEngine->processDownloadTransactionListPdf($startDate, $endDate, getVendorId(), $paymentType);
    }

    /**
     * Get Invoice details for Modal
     *
     * @param string $transactionIdOrUid
     * @return json
     */
    public function getInvoiceDetails($transactionIdOrUid)
    {
        $processReaction = $this->transactionEngine->prepareInvoiceHtml($transactionIdOrUid, getVendorId());
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Print Invoice page
     *
     * @param string $transactionIdOrUid
     * @return View
     */
    public function printInvoice($transactionIdOrUid)
    {
        $invoiceData = $this->transactionEngine->getInvoiceDetailsData($transactionIdOrUid, getVendorId());
        if (empty($invoiceData)) {
            abort(404, __tr('Transaction/Invoice not found'));
        }
        return $this->loadView('transaction.print-invoice', $invoiceData);
    }

    /**
     * Download Invoice PDF
     *
     * @param string $transactionIdOrUid
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadInvoice($transactionIdOrUid)
    {
        return $this->transactionEngine->processDownloadInvoicePdf($transactionIdOrUid, getVendorId());
    }
}
