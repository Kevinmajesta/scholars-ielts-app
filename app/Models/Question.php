<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = ['essay_id', 'question_text'];
    public function options()
    {
        return $this->hasMany(Option::class);
    }
    public function essay(): BelongsTo
    {
        return $this->belongsTo(Essay::class);
    }
}
