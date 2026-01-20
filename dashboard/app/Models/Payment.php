<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function qrisTransactionDetail()
    {
        return $this->morphOne(qrisTransactionDetail::class, 'transactionable');
    }

    public function qrisTransaction()
    {
        return $this->morphOne(QrisTransactionDetail::class, 'transactionable');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }


}