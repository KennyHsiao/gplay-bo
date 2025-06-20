<?php

namespace App\Models\Finance;

use App\Models\TxBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Helper\DateTransform;

class Adjustment extends TxBaseModel
{
    use HasFactory;

    protected $table = 'adjustments';

    public $timestamps = false;
    /**
     * 建立時間.
     *
     * @return string
     */
    public function getCreatedTimeAttribute($value) {
        return DateTransform::TimestampToLocal($value, 'Y-m-d H:i:s');
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            static::adjustOfTransaction($model);
            return false;
        });
    }

    /**
     * 調整玩家餘額
     *
     * @param [type] $model
     * @return void
     */
    protected static function adjustOfTransaction($model) {
        $txdb = strtolower(session('agent_code')."_tx");
        DB::connection($txdb)->getPdo()->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;');
        DB::connection($txdb)->transaction(function () use($txdb, $model) {
            $belongWeek = date('YW');
            $traceID = gen_trace_id("ADJ");
            $member = DB::connection($txdb)->select("SELECT * FROM members WHERE account = ? FOR UPDATE", [$model->account]);
            $beforeBalance = $member[0]->balance;
            $balance = $beforeBalance + $model->transfer_amount;
            DB::connection($txdb)->update("UPDATE members SET balance = balance + ? WHERE account = ?", [$model->transfer_amount, $model->account]);
            DB::connection($txdb)->insert("INSERT INTO transactions_{$belongWeek}(
                account, before_balance, transfer_amount, balance, trace_id, trans_type, created_time, belong_week, merchant_code
            ) VALUES(
                ?,?,?,?,?,?,?,?,?
            )", [
                $model->account,
                $beforeBalance,
                $model->transfer_amount,
                $balance,
                $traceID,
                "adjust",
                micro_timestamp(),
                $belongWeek,
                session('merchant_code')
            ]);
            DB::connection($txdb)->insert("INSERT INTO adjustments (
                trace_id, account, before_balance, transfer_amount, balance, memo, created_by, created_time, merchant_code
            ) VALUES(
                ?,?,?,?,?,?,?,?,?
            )", [
                $traceID,
                $model->account,
                $beforeBalance,
                $model->transfer_amount,
                $balance,
                $model->memo,
                $model->created_by,
                micro_timestamp(),
                session('merchant_code')
            ]);
        });
    }
}
