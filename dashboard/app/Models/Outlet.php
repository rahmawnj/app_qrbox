<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Gunakan class Authenticatable
use Illuminate\Notifications\Notifiable;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $casts = [
        'latlong' => 'array',
        'operational_hours' => 'array',
    ];
    

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function cashiers()
    {
        return $this->hasMany(Cashier::class);
    }

    public function addons()
    {
        return $this->hasMany(Addon::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getRoleAttribute()
    {
        return 'outlet';
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
