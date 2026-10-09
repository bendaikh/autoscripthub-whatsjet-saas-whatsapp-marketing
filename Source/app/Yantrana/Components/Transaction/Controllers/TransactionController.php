<?php
/**
* TransactionController.php - Controller file
*
* This file is part of the Transaction component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Transaction\Controllers;

use App\Yantrana\Base\BaseController;

use App\Yantrana\Base\BaseRequest;
use App\Yantrana\Base\BaseRequestTwo;
use App\Yantrana\Components\Transaction\TransactionEngine;

class TransactionController extends BaseController
{       /**
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
      * Show transactions list view
      *
      * @return View
      *---------------------------------------------------------------- */
    public function showTransactionListView(BaseRequestTwo $request)
    {
        if (!__isEmpty($request->input('duration'))) {
            $request->validate([
                'trx_start_date' => 'required|date|before_or_equal:trx_end_date',
                'trx_end_date' => 'required|date|after_or_equal:trx_start_date'
            ], [
                'trx_start_date.before_or_equal' => __tr('The start date must be a date before or equal to end date.'),
                'trx_end_date.after_or_equal' => __tr('The end date must be a date after or equal to start date.')
            ]);
        }

        return $this->loadView('transaction.list');
    }

    /**
      * Prepare transactions DataTable data
      *
      * @return json
      *---------------------------------------------------------------- */
    public function prepareTransactionList($paymentType = 'all', $startDate = null, $endDate = null)
    {
        return $this->transactionEngine->prepareTransactionDataTableSource($startDate, $endDate, null, $paymentType);
    }

    /**
      * Process transactions pdf download
      *
      * @return json
      *---------------------------------------------------------------- */
    public function processDownloadTransactionPdf($paymentType = 'all', $startDate = null, $endDate = null)
    {
        return $this->transactionEngine->processDownloadTransactionListPdf($startDate, $endDate, null, $paymentType);
    }

    /**
      * Delete transaction process
      *
      * @param string $transactionIdOrUid
      * @param BaseRequest $request
      *
      * @return json
      *---------------------------------------------------------------- */
    public function deleteTransaction($transactionIdOrUid, BaseRequest $request)
    {
        $processReaction = $this->transactionEngine->processTransactionDelete($transactionIdOrUid);
        if ($processReaction->success()) {
            return $this->processResponse(21, [], [
                'reloadPage' => true
            ], true);
        }
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Get Invoice details for Modal
     *
     * @param string $transactionIdOrUid
     * @return json
     */
    public function getInvoiceDetails($transactionIdOrUid)
    {
        $processReaction = $this->transactionEngine->prepareInvoiceHtml($transactionIdOrUid);
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
        $invoiceData = $this->transactionEngine->getInvoiceDetailsData($transactionIdOrUid);
        if (empty($invoiceData)) {
            abort(404, __tr('Transaction/Invoice not found'));
        }
        return $this->loadView('transaction.print-invoice', $invoiceData);
    }

    /**
     * Download Invoice PDF
     *
     * @param string $transactionIdOrUid
     * @return Response
     */
    public function downloadInvoice($transactionIdOrUid)
    {
        return $this->transactionEngine->processDownloadInvoicePdf($transactionIdOrUid);
    }

    /**
     * Payment Refund details
     *
     * @param string $transactionIdOrUid
     * @return json
     */
    public function paymentRefund($transactionIdOrUid)
    {
        $processReaction = $this->transactionEngine->preparePaymentRefundData($transactionIdOrUid);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
      * Refund transaction process
      *
      * @return View
      *---------------------------------------------------------------- */
    public function processRefund(BaseRequestTwo $request)
    {
        $request->validate([
            'transaction_id' => 'required',
            'refund_amount' => 'required|numeric|gt:0|decimal:0,2',
            'reference_id' => 'required|max:45|unique:transactions,reference_id',
            'notes' => 'nullable|max:500'
        ],
        [
            'reference_id.unique' => __tr('This reference Id already exists.'),
        ]);
        
        $processReaction = $this->transactionEngine->processPaymentRefund($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }
}