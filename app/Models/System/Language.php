<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 支援語系
 */
class Language extends Model
{
    use HasFactory;

    protected $table = 'sys_languages';

    public $timestamps = false;
}
