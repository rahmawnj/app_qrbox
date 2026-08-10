<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'time' => 'string', // Cukup gunakan 'string'
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function scopeForOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

 
    public function qrisTransaction()
    {
        return $this->hasOne(QrisTransactionDetail::class);
    }

    public function deviceTransactions()
    {
        return $this->hasMany(DeviceTransaction::class);
    }

    public function scopeUnfinishedServiceOrders($query)
    {
        $now = Carbon::now();

        return $query->where('type', 'manual')
            ->where(function ($q) use ($now) {
                $q->whereDoesntHave('deviceTransactions')
                    ->orWhereHas('deviceTransactions', function ($qInner) use ($now) {
                        $qInner->whereNull('activated_at')
                            ->orWhere('activated_at', '>', $now->subHours(24));
                    });
            });
    }

    public function dropOffTransaction()
    {
        return $this->hasOne(DropOffTransaction::class);
    }
    
    public function selfServiceTransaction()
    {
        return $this->hasOne(SelfServiceTransaction::class);
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
   
}