<?php

namespace App\Models\Merchant;

use App\Helpers\DateTransform;
use App\Models\Platform\Company;
use App\Models\Traits\PublishDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class Marquee extends Model
{
    use HasFactory, DefaultDatetimeFormat, PublishDate;

    protected $table = "mc_marquees";

    public function merchant() {
        return $this->belongsTo(Company::class, 'merchant_code', 'code');
    }

    /**
     * StartTime 10碼
     *
     * @param  string  $value
     * @return void
     */
    public function setStartTimeAttribute($value) {
        $this->attributes['start_time'] = DateTransform::DateToUTC($value, session('timezone'));
    }

    /**
     * EndTime 10碼
     *
     * @param  string  $value
     * @return void
     */
    public function setEndTimeAttribute($value) {
        $this->attributes['end_time'] = DateTransform::DateToUTC($value, session('timezone'));
    }
}
