<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'order_id', 'amount', 'status', 'payment_gateway', 'payment_id', 'reference_mode', 'reference_id', 'response_data'];
}
