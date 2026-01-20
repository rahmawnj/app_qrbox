<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropOffTransaction extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'addons' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}