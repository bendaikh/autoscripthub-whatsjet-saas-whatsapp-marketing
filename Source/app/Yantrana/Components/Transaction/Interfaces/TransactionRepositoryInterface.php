<?php
/**
* TransactionRepositoryInterface.php - Interface file
*
* This file is part of the Transaction component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Transaction\Interfaces;

interface TransactionRepositoryInterface
{
    /**
     * Fetch transactions stats for financial year
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function fetchTransactionsFinancialYearStats($startDate = null, $endDate = null);

    /**
     * Fetch transaction details by ID or UID
     *
     * @param string|int $idOrUid
     * @param int|string|null $vendorId
     * @return object|null
     */
    public function fetchTransactionDetails($idOrUid, $vendorId = null);

    /**
     * Calculate dashboard payments stats
     *
     * @return array
     */
    public function calculateDashboardPaymentStats();
}