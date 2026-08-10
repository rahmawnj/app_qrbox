<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'latlong' => 'array',
    ];
    /**
     * Get the user that owns the Member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function owners()
    {
        return $this->belongsToMany(Owner::class, 'subscription', 'member_id', 'owner_id')
            ->withPivot('is_verified', 'amount', 'created_at', 'updated_at')
            ->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}