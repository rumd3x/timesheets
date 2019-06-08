<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timestamp extends Model
{
    use SoftDeletes;

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
