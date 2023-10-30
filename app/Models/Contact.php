<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id','full_name','email','phone','dob','image'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        if(auth()->check()){
            $user = auth()->user()->load('role');
            if ($user->role->name == "user") {
                static::addGlobalScope('active', function ($builder) use($user) {
                    $builder->where('user_id', $user->id);
                });
            }
        }
    }
}
