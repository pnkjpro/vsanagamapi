<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TestQuestion extends Pivot
{
    protected $table = 'test_question';

    protected $fillable = [
        'test_id', 'question_id', 'order_id'
    ];
}
