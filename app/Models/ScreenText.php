<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreenText extends Model
{
    use HasFactory;
    protected $fillable = [
        'title1','desc1','title2','desc2','title3','desc3'
    ];
}
