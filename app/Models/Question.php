<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['topic_id', 'nid', 'qid', 'question_text_en', 'question_text_hi', 'explanation', 'difficulty'];
}
