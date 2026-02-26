<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Essay extends Model
{
    protected $fillable = ['title', 'content'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
