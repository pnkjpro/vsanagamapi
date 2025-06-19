<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = ['course_id', 'subject_id', 'name', 'type', 'is_free', 'time_limit', 'total_marks', 'description'];
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'test_question')
                    ->using(TestQuestion::class)
                    ->withPivot('order')
                    ->withTimestamps();
    }

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'test_question')
                    ->using(TestQuestion::class)
                    ->withPivot('order')
                    ->withTimestamps();
    }
}
