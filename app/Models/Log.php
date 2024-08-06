<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'interrupt_time',
    ];

    protected $casts = [
        'interrupt_time' => 'datetime',
    ];

}
