<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $fillable = ['user_id', 'essay_id', 'total_questions', 'correct_answers', 'score', 'details'];
    protected $casts = ['details' => 'array']; // Otomatis konversi JSON ke Array
}
