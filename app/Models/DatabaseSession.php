<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseSession extends Model
{
    use HasFactory;

    protected $table = 'admin_sessions';
}
