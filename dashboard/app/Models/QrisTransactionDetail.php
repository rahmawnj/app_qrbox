<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrisTransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'qris_transactions';

    protected $guarded = [
    ];

    /**
     * Get the payment that owns the QRIS transaction detail.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

   public function transactionable()
    {
        return $this->morphTo();
    }
}