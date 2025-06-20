<?php

namespace App\Models\Merchant;

use App\Models\Platform\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class Banner extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    protected $table = "mc_banners";

    public function merchant() {
        return $this->belongsTo(Company::class, 'merchant_code', 'code');
    }
}
