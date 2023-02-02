<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'relation_id',
        'routing_number',
        'account_number',
        'owner_name',
        'nickname',
        'status'
    ];

    protected $hidden = ['updated_at'];
}
