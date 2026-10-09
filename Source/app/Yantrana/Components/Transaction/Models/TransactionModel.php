<?php
/**
* Transaction.php - Model file
*
* This file is part of the Transaction component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Transaction\Models;

use App\Yantrana\Base\BaseModel;

use App\Yantrana\Components\Vendor\Models\VendorModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionModel extends BaseModel
{
    /**
     * @var string $table - The database table used by the model.
     */
    protected $table = "transactions";

    /**
     * @var array $casts - The attributes that should be casted to native types.
     */
    protected $casts = [
        'amount' => 'decimal:4',
        '__data' => 'array',
    ];

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'transaction_data' => 'array:extend',
        ],
    ];

    /**
     * @var array $fillable - The attributes that are mass assignable.
     */
    protected $fillable = [
    ];

    /**
     * Get the vendor that owns the transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorModel::class, 'vendors__id', '_id');
    }
}