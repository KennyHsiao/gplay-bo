<?php

namespace App\Models\System;

use App\Models\Platform\Merchant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * 數據庫
 */
class Database extends Model
{
    use HasFactory;

    protected $table = 'sys_databases';

    protected $guarded = [];

    public $timestamps = false;

    public function getTxDbAttribute()
    {
        return json_decode($this->attributes['tx_db'], true);
    }

    public function getRepDbAttribute()
    {
        return json_decode($this->attributes['rep_db'], true);
    }

    protected static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            Redis::del("database_{$model->agent_code}");
            Cache::forget("database_{$model->agent_code}");
        });

        self::deleting(function ($model) {
            Redis::del("database_{$model->agent_code}");
            Cache::forget("database_{$model->agent_code}");
        });
    }
}
