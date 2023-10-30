<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'contact_id', 'name', 'desc', 'date'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
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
