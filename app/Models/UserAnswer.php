<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $fillable = ['test_attempt_id', 'question_id', 'selected_option_id', 'is_correct'];
}
