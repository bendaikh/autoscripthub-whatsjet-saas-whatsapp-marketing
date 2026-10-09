<?php
/**
* TransactionRepository.php - Repository file
*
* This file is part of the Transaction component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Transaction\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Transaction\Models\TransactionModel;
use App\Yantrana\Components\Transaction\Interfaces\TransactionRepositoryInterface;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TransactionRepository extends BaseRepository
                          implements TransactionRepositoryInterface
{
    /**
     * primary model instance
     *
     * @var object
     */
        protected $primaryModel = TransactionModel::class;

    /**
      * Fetch transaction datatable source
      *
      * @param string|null $startDate
      * @param string|null $endDate
      *
      * @return object|array
      *---------------------------------------------------------------- */
    public function fetchTransactionDataTableSource($startDate, $endDate, $getRawData, $vendorId = null, $paymentType = null)
    {
        $dataTableConfig = [
            'fieldAlias' => [
                'created_at' => 'transactions.created_at',
                'vendor_title' => 'vendors.title',
                'formatted_type' => 'transactions.type'
            ],
            'searchable' => [
                'vendors.title',
                'transactions.type',
                'transactions.amount',
                'transactions.reference_id',
                'transactions.notes',
                'transactions.status'
            ]
        ];
        
        $transactionModel = TransactionModel::select(DB::raw('transactions.*, vendors.title as vendor_title, vendors._uid as vendor_uid, manual_subscriptions.__data as manual_subscription_data, manual_subscriptions.plan_id as manual_subscription_plan_id, subscriptions.type as subscription_plan_id'))
            ->leftJoin('vendors', 'transactions.vendors__id', '=', 'vendors._id')
            ->leftJoin('manual_subscriptions', 'transactions.manual_subscriptions__id', '=', 'manual_subscriptions._id')
            ->leftJoin('subscriptions', 'transactions.subscriptions_id', '=', 'subscriptions.id')
            ->when(!empty($startDate) and !empty($endDate), function ($query) use ($startDate, $endDate) {
                $query->whereBetween('transactions.created_at', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ]);
            })
            ->when(!empty($vendorId), function ($query) use ($vendorId) {
                $query->where('transactions.vendors__id', $vendorId);
            })
            ->when(!empty($paymentType) and strtolower($paymentType) !== 'all', function ($query) use ($paymentType) {
                $query->where('transactions.type', strtoupper($paymentType));
            });

        if ($getRawData) {
            return $transactionModel->get();
        } else {
            return $transactionModel->dataTables($dataTableConfig)->toArray();
        }
    }

    /**
     * Fetch monthly transaction counts for the current financial year
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function fetchTransactionsFinancialYearStats($startDate = null, $endDate = null)
    {
        if (! $startDate) {
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        } elseif (! $startDate instanceof Carbon) {
            $startDate = Carbon::parse($startDate)->startOfMonth();
        } else {
            $startDate = $startDate->copy()->startOfMonth();
        }

        if (! $endDate) {
            $endDate = Carbon::now()->endOfMonth();
        } elseif (! $endDate instanceof Carbon) {
            $endDate = Carbon::parse($endDate)->endOfMonth();
        } else {
            $endDate = $endDate->copy()->endOfMonth();
        }

        $diffInMonths = $startDate->diffInMonths($endDate);

        $allTheMonths = collect(range(0, $diffInMonths))->map(function ($i) use ($startDate) {
            $dt = $startDate->copy()->addMonths($i);

            return [
                'total_amount' => 0,
                'month_name' => $dt->translatedFormat('M').' '.__tr($dt->translatedFormat('Y')),
                'month' => $dt->shortMonthName,
                'year' => $dt->year,
                'month_number' => $dt->month,
                'month_year' => $dt->month.'-'.$dt->year,
            ];
        });

        $dataRecords = $this->primaryModel::select(
            DB::raw("SUM(amount) AS total_amount"),
            DB::raw("DATE_FORMAT(created_at, '%c-%Y') month_year"),
            DB::raw("DATE_FORMAT(created_at, '%Y') year"),
            DB::raw('MONTH(created_at) month_number')
        )
            ->where('status', 1)
            ->whereBetween('transactions.created_at', [
                $startDate,
                $endDate,
            ])
            ->groupBy('month_year', 'year', 'month_number')
            ->orderBy('year')
            ->orderBy('month_number')
            ->get()
            ->map(function ($record) {
                $record->total_amount = (float) $record->total_amount;
                return $record;
            });

        return arrayExtend(
            __reIndexArray($allTheMonths->toArray(), 'month_year'),
            __reIndexArray($dataRecords->toArray(), 'month_year')
        );
    }

    /**
     * Fetch transaction details by ID or UID
     *
     * @param string|int $idOrUid
     * @return object|null
     */
    public function fetchTransactionDetails($idOrUid, $vendorId = null)
    {
        return TransactionModel::select(DB::raw('transactions.*, vendors.title as vendor_title, vendors._uid as vendor_uid, manual_subscriptions.__data as manual_subscription_data'))
            ->leftJoin('vendors', 'transactions.vendors__id', '=', 'vendors._id')
            ->leftJoin('manual_subscriptions', 'transactions.manual_subscriptions__id', '=', 'manual_subscriptions._id')
            ->leftJoin('subscriptions', 'transactions.subscriptions_id', '=', 'subscriptions.id')
            ->where(function ($query) use ($idOrUid) {
                if (is_numeric($idOrUid)) {
                    $query->where('transactions._id', $idOrUid);
                } else {
                    $query->orWhere('transactions._uid', $idOrUid);
                }
            })
            ->when(!empty($vendorId), function ($query) use ($vendorId) {
                $query->where('transactions.vendors__id', $vendorId);
            })
            ->first();
    }

    /**
     * Fetch latest transaction for a manual subscription.
     *
     * @param string|int $manualSubscriptionId
     * @return object|null
     */
    public function fetchTransactionForManualSubscription($manualSubscriptionId)
    {
        return TransactionModel::select(DB::raw('transactions.*, vendors.title as vendor_title, vendors._uid as vendor_uid, manual_subscriptions.__data as manual_subscription_data'))
            ->leftJoin('vendors', 'transactions.vendors__id', '=', 'vendors._id')
            ->leftJoin('manual_subscriptions', 'transactions.manual_subscriptions__id', '=', 'manual_subscriptions._id')
            ->leftJoin('subscriptions', 'transactions.subscriptions_id', '=', 'subscriptions.id')
            ->where('transactions.manual_subscriptions__id', $manualSubscriptionId)
            ->where('transactions.status', 1)
            ->orderBy('transactions.created_at', 'desc')
            ->first();
    }

    /**
     * Fetch latest transaction for a Stripe subscription.
     *
     * @param string|int $subscriptionId
     * @return object|null
     */
    public function fetchTransactionForStripeSubscription($subscriptionId)
    {
        return TransactionModel::select(DB::raw('transactions.*, vendors.title as vendor_title, vendors._uid as vendor_uid, manual_subscriptions.__data as manual_subscription_data'))
            ->leftJoin('vendors', 'transactions.vendors__id', '=', 'vendors._id')
            ->leftJoin('manual_subscriptions', 'transactions.manual_subscriptions__id', '=', 'manual_subscriptions._id')
            ->leftJoin('subscriptions', 'transactions.subscriptions_id', '=', 'subscriptions.id')
            ->where('transactions.subscriptions_id', $subscriptionId)
            ->where('transactions.status', 1)
            ->orderBy('transactions.created_at', 'desc')
            ->first();
    }

    /**
     * Calculate dashboard payments stats
     *
     * @return array
     */
    public function calculateDashboardPaymentStats()
    {
        // 1. Calculate Payments in This Month (C)
        $totalPaymentsThisMonth = $this->primaryModel::where('status', 1)
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->sum('amount');

        return [
            'payments_this_month' => $totalPaymentsThisMonth,
        ];
    }

    /**
     * Fetch refunded transactions
     *
     * @param string|int $transactionId
     * @return object
     */
    public function fetchRefundedTransactions($transactionId)
    {
        return $this->primaryModel::where(
                '__data->transaction_data->original_transaction_id',
                $transactionId
            )
            ->get();
    }
}