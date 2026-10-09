<?php
/**
* TransactionEngine.php - Main component file
*
* This file is part of the Transaction component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Transaction;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\Transaction\Repositories\TransactionRepository;
use App\Yantrana\Components\Subscription\Repositories\ManualSubscriptionRepository;
use App\Yantrana\Components\Subscription\Repositories\SubscriptionRepository;
use App\Yantrana\Components\Transaction\Interfaces\TransactionEngineInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TransactionEngine extends BaseEngine implements TransactionEngineInterface
{
    /**
     * @var TransactionRepository $transactionRepository - Transaction Repository
     */
    protected $transactionRepository;

    /**
     * @var  ManualSubscriptionRepository $manualSubscriptionRepository - ManualSubscription Repository
     */
    protected $manualSubscriptionRepository;

    /**
     * @var SubscriptionRepository - Subscription Repository
     */
    protected $subscriptionRepository;

    /**
      * Constructor
      *
      * @param TransactionRepository $transactionRepository - Transaction Repository
      * @param  ManualSubscriptionRepository $manualSubscriptionRepository - ManualSubscription Repository
      * @param  SubscriptionRepository  $subscriptionRepository  - Subscription Repository
      *
      * @return void
      *-----------------------------------------------------------------------*/

    function __construct(
        TransactionRepository $transactionRepository, 
        ManualSubscriptionRepository $manualSubscriptionRepository,
        SubscriptionRepository $subscriptionRepository
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->manualSubscriptionRepository = $manualSubscriptionRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
      * Prepare transaction DataTable source
      *
      * @return array
      *---------------------------------------------------------------- */
    public function prepareTransactionDataTableSource($startDate = null, $endDate = null, $vendorId = null, $paymentType = null)
    {
        $transactionCollection = $this->transactionRepository->fetchTransactionDataTableSource($startDate, $endDate, false, $vendorId, $paymentType);
        $statusCodes = configItem('status_codes');
        $transactionTypes = configItem('transaction_types');
        $refundTypes = configItem('refund_types');
        $allPaidPlans = getPaidPlans();
        
        $requireColumns = [
            '_id',
            '_uid',
            'created_at' => function ($rowData) {
                return formatDate($rowData['created_at']);
            },
            'vendor_title' => function ($rowData) {
                return $rowData['vendor_title'] ?? '';
            },
            'vendor_uid' => function ($rowData) {
                return $rowData['vendor_uid'] ?? '';
            },
            'type' => function ($rowData) {
                return $rowData['type'];
            },
            'formatted_type' => function ($rowData) use ($transactionTypes) {
                return $transactionTypes[$rowData['type']] ?? 'N/A';
            },
            'payment_method' => function ($rowData) {
                if (!empty($rowData['manual_subscriptions__id'])) {
                    $manualData = $rowData['manual_subscription_data'] ?? null;
                    if (is_string($manualData)) {
                        $manualData = json_decode($manualData, true);
                    }
                    $paymentMethod = data_get($manualData, 'manual_txn_details.selected_payment_method');
                    return !empty($paymentMethod) ? Str::headline($paymentMethod) : '-';
                } elseif (!empty($rowData['subscriptions__id']) || !empty($rowData['subscriptions_id'])) {
                    return 'Stripe';
                }
                return '-';
            },
            'amount' => function ($rowData) {
                return formatAmount($rowData['amount'], true, true);
            },
            'reference_id',
            'status' => function ($rowData) use ($statusCodes) {
                return Arr::get($statusCodes, $rowData['status'], $rowData['status'] ?? '');
            },
            'plan' => function($row) use ($allPaidPlans) {
                if (!__isEmpty($row['manual_subscription_plan_id'])) {
                    return data_get($allPaidPlans, $row['manual_subscription_plan_id'].'.title');
                } elseif (!__isEmpty($row['subscription_plan_id'])) {
                    return data_get($allPaidPlans, $row['subscription_plan_id'].'.title');
                }
                return '';
            },
            'formatted_refund_type' => function($rowData) use ($refundTypes) {
                if (!__isEmpty(data_get($rowData, '__data.transaction_data.refund_status'))) {
                    return $refundTypes[$rowData['__data']['transaction_data']['refund_status']] ?? '';
                }

                return '';
            },
            'refund_type' => function($rowData) {
                return $rowData['__data']['transaction_data']['refund_status'] ?? '';
            },
            'notes'
        ];
        
        return $this->dataTableResponse($transactionCollection, $requireColumns);
    }

    /**
      * Prepare transaction DataTable source
      *
      * @return array
      *---------------------------------------------------------------- */
    public function processDownloadTransactionListPdf($startDate = null, $endDate = null, $vendorId = null, $paymentType = null)
    {
        $transactionCollection = $this->transactionRepository->fetchTransactionDataTableSource($startDate, $endDate, true, $vendorId, $paymentType);
        $statusCodes = configItem('status_codes');
        $paymentMethods = configItem('subscription_payments_methods');
        $allPaidPlans = getPaidPlans();

        $transactions = $transactionCollection->map(function ($rowData) use ($paymentMethods, $statusCodes, $allPaidPlans) {
            $rowData = is_object($rowData) && method_exists($rowData, 'toArray') ? $rowData->toArray() : (array) $rowData;

            $manualData = $rowData['manual_subscription_data'] ?? null;
            if (is_string($manualData)) {
                $manualData = json_decode($manualData, true);
            }

            $paymentMethod = '-';
            if (!empty($rowData['manual_subscriptions__id'])) {
                $selectedPaymentMethod = data_get($manualData, 'manual_txn_details.selected_payment_method');
                $paymentMethod = !empty($selectedPaymentMethod) ? $selectedPaymentMethod : '-';
            } elseif (!empty($rowData['subscriptions__id']) || !empty($rowData['subscriptions_id'])) {
                $paymentMethod = 'Stripe';
            }

            $plan = '';
            if (!__isEmpty($rowData['manual_subscription_plan_id'])) {
                $plan = data_get($allPaidPlans, $rowData['manual_subscription_plan_id'].'.title');
            } elseif (!__isEmpty($rowData['subscription_plan_id'])) {
                $plan = data_get($allPaidPlans, $rowData['subscription_plan_id'].'.title');
            }

            return [
                'created_at' => formatDate($rowData['created_at'] ?? ''),
                'vendor_title' => $rowData['vendor_title'] ?? '',
                'vendor_uid' => $rowData['vendor_uid'] ?? '',
                'type' => Arr::get($paymentMethods, strtolower($rowData['type'] ?? ''), $rowData['type'] ?? ''),
                'payment_method' => Str::headline($paymentMethod),
                'amount' => formatAmount($rowData['amount'] ?? 0, true, true),
                'reference_id' => $rowData['reference_id'] ?? '',
                'status' => Arr::get($statusCodes, $rowData['status'] ?? '', $rowData['status'] ?? ''),
                'notes' => $rowData['notes'] ?? '',
                'plan' => $plan
            ];
        })->values();
        
        $durationLabel = 'All Transactions';
        if (!empty($startDate) && !empty($endDate)) {
            $durationLabel = __tr('From') . ' ' . formatDate($startDate) . ' ' . __tr('to') . ' ' . formatDate($endDate);
        } elseif (!empty($startDate)) {
            $durationLabel = __tr('From') . ' ' . formatDate($startDate);
        } elseif (!empty($endDate)) {
            $durationLabel = __tr('Until') . ' ' . formatDate($endDate);
        }

        $isVendor = (!__isEmpty($vendorId)) ? true : false;

        $html = view('transaction.pdf', compact('transactions', 'durationLabel', 'isVendor'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        $fileName = 'transactions-report-' . str_replace([' ', '/', '\\', ':'], '-', $durationLabel);
        $fileName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $fileName);
        $fileName = trim($fileName, '-');

        // To view directly on browser
        // return response($pdf->output(), 200)
        //     ->header('Content-Type', 'application/pdf')
        //     ->header('Content-Disposition', 'inline; filename="' . $fileName . '.pdf"');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName . '.pdf');
    }

    /**
      * Transaction delete process
      *
      * @param mix $transactionIdOrUid
      *
      * @return EngineResponse
      *---------------------------------------------------------------- */
    public function processTransactionDelete($transactionIdOrUid)
    {
        $transaction = $this->transactionRepository->fetchIt($transactionIdOrUid);
        if (__isEmpty($transaction)) {
            return $this->engineResponse(18, null, __tr('Transaction not found'));
        }
        if ($this->transactionRepository->deleteIt($transaction)) {
            return $this->engineResponse(1, null, __tr('Transaction deleted successfully'));
        }
        return $this->engineResponse(2, null, __tr('Failed to delete transaction'));
    }

    /**
     * Get Invoice details data
     *
     * @param string|int $transactionIdOrUid
     * @return array|null
     */
    public function getInvoiceDetailsData($transactionIdOrUid, $vendorId = null)
    {
        $transaction = $this->transactionRepository->fetchTransactionDetails($transactionIdOrUid, $vendorId);
        if (__isEmpty($transaction)) {
            return null;
        }

        $isRefund = ($transaction->type === 'REFUND');

        // Fetch vendor admin email
        $buyerEmail = '';
        if (!empty($transaction->vendors__id)) {
            $primaryUser = \App\Yantrana\Components\Auth\Models\AuthModel::where('vendors__id', $transaction->vendors__id)->first();
            if ($primaryUser) {
                $buyerEmail = $primaryUser->email;
            }
        }

        // Settings
        $brandName = getAppSettings('invoice_brand_name') ?: getAppSettings('name') ?: 'WhatsJet';
        $brandAddress = getAppSettings('invoice_address') ?: '';
        $additionalInfo = getAppSettings('invoice_additional_info') ?: '';
        $taxLabel = getAppSettings('tax_label') ?: '';
        // Rule 1: Use transaction saved tax rate if present in __data; Rule 2: Fallback to Billing & Invoice settings
        $savedTaxRate = data_get($transaction->__data, 'tax_rate');
        if (is_null($savedTaxRate)) {
            $savedTaxRate = data_get($transaction->__data, 'transaction_data.tax_rate');
        }
        if (!is_null($savedTaxRate) && $savedTaxRate !== '') {
            $taxRate = floatval($savedTaxRate);
        } else {
            $taxRate = floatval(getAppSettings('tax_rate'));
        }
        $logoUrl = getAppSettings('invoice_logo_image_url') ?: '';

        // Plan / Product Info
        $planTitle = __tr('Subscription');
        $planFrequency = '';
        $paymentMethod = '-';

        // Load configs
        $paidPlans = config('lw-plans.paid', []);
        $expiryDate = null;

        if (!empty($transaction->manual_subscriptions__id)) {
            $manualData = $transaction->manual_subscription_data;
            if (is_string($manualData)) {
                $manualData = json_decode($manualData, true);
            }
            $paymentMethod = data_get($manualData, 'manual_txn_details.selected_payment_method');
            if (empty($paymentMethod)) {
                $paymentMethod = __tr('Manual');
            }

            // Get plan from manual subscription
            $manualSub = $this->manualSubscriptionRepository->fetchIt($transaction->manual_subscriptions__id);
            if ($manualSub) {
                $planId = $manualSub->plan_id;
                $planFrequency = $manualSub->charges_frequency ?: '';
                if ($planId && isset($paidPlans[$planId])) {
                    $planTitle = $paidPlans[$planId]['title'] ?? $planId;
                }
                $expiryDate = $manualSub->ends_at;
            }
        } elseif (!empty($transaction->subscriptions_id)) {
            $paymentMethod = 'Stripe';
            $subscription = $this->subscriptionRepository->fetchIt($transaction->subscriptions_id);
            if ($subscription) {
                $planId = $subscription->type;
                if ($planId && isset($paidPlans[$planId])) {
                    $planTitle = $paidPlans[$planId]['title'] ?? $planId;
                    
                    // Match price ID to find frequency
                    $planCharges = $paidPlans[$planId]['charges'] ?? [];
                    foreach ($planCharges as $freqKey => $chargeVal) {
                        if (($chargeVal['price_id'] ?? null) == $subscription->stripe_price) {
                            $planFrequency = $freqKey;
                            break;
                        }
                    }
                }
                $expiryDate = $subscription->ends_at;
            }
        }

        // Original transaction reference for refund/credit note
        $originalTransaction = null;
        $originalInvoiceId = null;
        if ($isRefund) {
            $txnData = is_array($transaction->__data) ? $transaction->__data : (is_string($transaction->__data) ? json_decode($transaction->__data, true) : []);
            $originalTransactionId = data_get($txnData, 'transaction_data.original_transaction_id');
            if (!empty($originalTransactionId)) {
                $originalTransaction = $this->transactionRepository->fetchTransactionDetails($originalTransactionId, $vendorId);
                if ($originalTransaction) {
                    $originalInvoiceId = Str::of($originalTransaction->_uid)
                        ->replace('-', '')
                        ->substr(0, 20)
                        ->toString();
                }
            }
        }

        // Calculations
        $amount = floatval($transaction->amount);
        if ($taxRate > 0) {
            $subtotal = $amount / (1 + ($taxRate / 100));
            $taxAmount = $amount - $subtotal;
        } else {
            $subtotal = $amount;
            $taxAmount = 0.0;
        }

        $documentTitle = $isRefund ? __tr('Credit Note') : __tr('Invoice');

        return [
            'isRefund' => $isRefund,
            'documentTitle' => $documentTitle,
            'transaction' => $transaction,
            'originalTransaction' => $originalTransaction,
            'originalInvoiceId' => $originalInvoiceId,
            'buyerEmail' => $buyerEmail,
            'brandName' => $brandName,
            'brandAddress' => $brandAddress,
            'additionalInfo' => $additionalInfo,
            'taxLabel' => $taxLabel,
            'taxRate' => $taxRate,
            'logoUrl' => $logoUrl,
            'planTitle' => $planTitle,
            'planFrequency' => $planFrequency,
            'paymentMethod' => $paymentMethod,
            'formattedAmount' => formatAmount($transaction->amount, true, true),
            'subtotal' => $subtotal,
            'taxAmount' => $taxAmount,
            'amount' => $amount,
            'expiryDate' => (!__isEmpty($expiryDate)) ? formatDate($expiryDate) : __tr('N/A'),
            'invoiceId' => Str::of($transaction->_uid)
                            ->replace('-', '')
                            ->substr(0, 20)
                            ->toString()
        ];
    }

    /**
     * Prepare invoice HTML for dialog response
     *
     * @param string|int $transactionIdOrUid
     * @return EngineResponse
     */
    public function prepareInvoiceHtml($transactionIdOrUid, $vendorId = null)
    {
        $invoiceData = $this->getInvoiceDetailsData($transactionIdOrUid, $vendorId);

        if (empty($invoiceData)) {
            return $this->engineResponse(18, null, __tr('Transaction/Invoice not found'));
        }

        $html = view('transaction.invoice', $invoiceData)->render();
        return $this->engineSuccessResponse([
            'invoiceHtml' => $html
        ]);
    }

    /**
     * Generate and stream PDF invoice download
     *
     * @param string|int $transactionIdOrUid
     * @return Response
     */
    public function processDownloadInvoicePdf($transactionIdOrUid, $vendorId = null)
    {
        $invoiceData = $this->getInvoiceDetailsData($transactionIdOrUid, $vendorId);
        if (empty($invoiceData)) {
            abort(404, __tr('Transaction/Invoice not found'));
        }

        $invoiceData['isPdf'] = true;
        $html = view('transaction.invoice', $invoiceData)->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $prefix = !empty($invoiceData['isRefund']) ? 'credit-note-' : 'invoice-';
        $fileName = $prefix . ($invoiceData['transaction']->reference_id ?? $transactionIdOrUid);
        $fileName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $fileName);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName . '.pdf');
    }

    /**
     * Prepare invoice HTML for dialog response
     *
     * @param string|int $transactionIdOrUid
     * @return EngineResponse
     */
    public function preparePaymentRefundData($transactionIdOrUid)
    {
        $transactionDetails = $this->getInvoiceDetailsData($transactionIdOrUid);

        if (empty($transactionDetails)) {
            return $this->engineResponse(18, null, __tr('Transaction not found'));
        }

        $refundAmount = $transactionDetails['amount'];
        
        if (!__isEmpty($transactionDetails['transaction']['__data']['transaction_data']['is_refunded'] ?? null)) {
            $refundedTransactions = $this->transactionRepository->fetchRefundedTransactions($transactionDetails['transaction']['_id']);
            $refundAmount = $refundAmount - $refundedTransactions->sum('amount');
        }

        return $this->engineSuccessResponse([
            'transactionDetails' => $transactionDetails,
            'refundAmount' => round($refundAmount, 2)
        ]);
    }

    /**
     * Prepare invoice HTML for dialog response
     *
     * @param array|int $inputData
     * @return EngineResponse
     */
    public function processPaymentRefund($inputData)
    {
        $transactionDetails = $this->transactionRepository->fetchIt($inputData['transaction_id']);

        if (empty($transactionDetails)) {
            return $this->engineResponse(18, null, __tr('Transaction not found'));
        }

        $refundAmount = $transactionDetails->amount;

        $refundedTransactions = $this->transactionRepository->fetchRefundedTransactions($transactionDetails->_id);
        if (!__isEmpty($refundedTransactions)) {
            $refundAmount = round($refundAmount - $refundedTransactions->sum('amount'), 2);
        }

        if ($inputData['refund_amount'] > $refundAmount) {
            return $this->engineResponse(18, null, __tr('Refund amount may not greater than transaction amount.'));
        }

        $refundType = 'PARTIAL_REFUND';

        if ($inputData['refund_amount'] == $refundAmount) {
            $refundType = 'FULL_REFUND';
        }

        $savedTaxRate = data_get($transactionDetails->__data, 'tax_rate');
        if (is_null($savedTaxRate)) {
            $savedTaxRate = data_get($transactionDetails->__data, 'transaction_data.tax_rate');
        }
        $taxRate = !is_null($savedTaxRate) && $savedTaxRate !== '' ? floatval($savedTaxRate) : floatval(getAppSettings('tax_rate'));

        if ($this->transactionRepository->storeIt([
            'status' => 1,
            'amount' => $inputData['refund_amount'],
            'reference_id' => $inputData['reference_id'],
            'notes' => $inputData['notes'],
            '__data' => [
                'transaction_data' => [
                    'original_transaction_id' => $inputData['transaction_id']
                ],
                'tax_rate' => $taxRate,
            ],
            'vendors__id' => $transactionDetails->vendors__id,
            'subscriptions_id' => $transactionDetails->subscriptions_id,
            'manual_subscriptions__id' => $transactionDetails->manual_subscriptions__id,
            'credit_transactions__id' => $transactionDetails->credit_transactions__id,
            'type' => 'REFUND'
        ])) {
            $this->transactionRepository->updateIt($transactionDetails, [
                '__data' => [
                    'transaction_data' => [
                        'is_refunded' => true,
                        'refund_status' => $refundType
                    ]
                ],
            ]);

            return $this->engineResponse(1, null, __tr('Transaction refunded.'));
        }

        return $this->engineResponse(2, null, __tr('Transaction not refunded.'));
    }
}