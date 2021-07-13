<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'purpose', 'isVerified', 'path', 'paid_from'
    ];
}
