<?php

namespace App\Models\Platform;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;
use Xn\Admin\Traits\AdminBuilder;
use Xn\Admin\Traits\ModelTree;
use Xn\Admin\Traits\SelectOptGroup;
use Xn\Admin\Tree;

class Company extends UuidModel
{
    use HasFactory, ModelTree, AdminBuilder, SelectOptGroup;

    protected $table = "sys_companies";

    public function merchants() {
        return $this->hasMany(static::class, 'parent_id', 'id');
    }

    public function merchant() {
        return $this->hasOne(Merchant::class, 'code', 'code');
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setParentColumn('parent_id');
        $this->setTitleColumn('name');
    }

    public function scopeFilterMerchant($query) {
        return $query->select(DB::raw("CONCAT('(',code,')',name) AS name"), "code")
        ->where('type', 'merchant')
        ->orderBy("code")->get();
    }

    public function scopePluckKV($query) {
        return $query->select(DB::raw("CONCAT('(',code,')',name) AS name"), "code")
        ->orderBy("code")->pluck('name', 'code');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Model $branch) {
            $cacheKey = "company_{$branch->code}_ip";
            Cache::forget($cacheKey);

            $branch->code = strtoupper($branch->code);
            $parentColumn = $branch->getParentColumn();

            if (Request::has($parentColumn) && Request::input($parentColumn) == $branch->getKey()) {
                throw new \Exception(trans('admin.parent_select_error'));
            }

            if (Request::has('_order')) {
                $order = Request::input('_order');

                Request::offsetUnset('_order');

                (new Tree(new static()))->saveOrder($order);

                return false;
            }

            return $branch;
        });

        self::saving(function ($model) {
            $cacheKey = "merchant_" . $model->code;
            Redis::del($cacheKey);
            // Redis::del("{$model->line_login_channel_id}_switch");
            Cache::forget("{$model->code}_switch");
        });
        self::deleting(function ($model) {
            $cacheKey = "merchant_" . $model->code;
            Redis::del($cacheKey);
            // Redis::del("{$model->line_login_channel_id}_switch");
            Cache::forget("{$model->code}_switch");
        });
    }
}
