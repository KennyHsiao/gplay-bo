<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 支援幣別
 */
class Currency extends Model
{
    use HasFactory;

    protected $table = 'sys_currencies';

    public $timestamps = false;
}
