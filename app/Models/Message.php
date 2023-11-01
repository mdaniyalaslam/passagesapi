<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'contact_id',
        'gift_id',
        'message',
        'video',
        'voice',
        'event_name',
        'event_desc',
        'schedule_date',
        'is_read',
        'is_schedule',
        'is_draft',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class, 'gift_id', 'id');
    }
}
