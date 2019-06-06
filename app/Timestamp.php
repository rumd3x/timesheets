<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timestamp extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'time',
        'entry',
    ];

    protected $casts = [
        'entry' => 'boolean',
    ];

    public function user()
    {
        $this->belongsTo(User::class);
    }
}
