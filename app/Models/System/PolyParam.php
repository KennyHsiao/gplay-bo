<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class PolyParam extends Model
{
    public $timestamps = false;
    public $table = 'admin_poly_params';

    protected $fillable = [
        'merchant_code',
        'param_slug',
        'param_value',
        'poly_id',
        'poly_type'
    ];
    /**
     * Get all of the owning param models.
     */
    public function param()
    {
        return $this->morphTo();
    }

    /**
     * 序列化屬性.
     *
     * @param  string  $value
     * @return void
     */

    public function getParamValueAttribute()
    {
        if (strpos($this->attributes['param_value'], '/&') != 0) {
            $values = explode('/&', $this->attributes['param_value']);
            return array_filter($values, function($value) { return $value !== ''; });
        } else {
            return $this->attributes['param_value'];
        }
    }

    public function setParamValueAttribute($value)
    {
        if (gettype($value) == 'array') {
            $this->attributes['param_value'] = implode('/&', $value);
        } else {
            $this->attributes['param_value'] = $value;
        }
    }
}
