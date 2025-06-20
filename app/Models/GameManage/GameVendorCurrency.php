<?php

namespace App\Models\GameManage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class GameVendorCurrency extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    public $guarded = [];

    protected $table = "gm_vendor_currencies";

}
