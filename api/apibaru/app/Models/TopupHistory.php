<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopupHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = [
        'topup_time',
    ];
    protected $casts = [
        'time' => 'datetime',
    ];

    /**
     * Relasi ke model Member.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Relasi ke model Outlet.
     */
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function qrisTransaction()
    {
        return $this->morphOne(QrisTransactionDetail::class, 'transactionable');
    }
}