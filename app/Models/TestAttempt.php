<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestAttempt extends Model
{
    protected $fillable = ['user_id', 'test_id', 'start_time', 'end_time', 'score', 'status'];
}
