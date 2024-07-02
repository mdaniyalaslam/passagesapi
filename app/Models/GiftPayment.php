<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'receiver_id', 'gift_id', 'stripe_id', 'amount', 'payment_method', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }
}
