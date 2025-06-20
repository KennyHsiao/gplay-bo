<?php


namespace App\Services;


use App\Helpers\RedisMutexLock;
use App\Http\HttpResponse\RespData;
use App\Http\HttpResponse\RespState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionService
{

    public static function transferIn($merchantCode, $account, $amount, $memo = null, $transType = 'transferIn') {
        $amount = floatval($amount);
        // 交易金額必須 >= 0
        if ($amount < 0) {
            return (new RespData(RespState::TransferAmountError, __('交易金額錯誤')))->toJson();
        }
        $orderNo = Self::GenOrderNo();

        $redisKey = "tx_lock_". md5($account);
        $lock = RedisMutexLock::lock($redisKey, $orderNo);

        if ($lock) {

            $txDb = strtolower(session('agent_code')."_tx");
            try {
                DB::connection($txDb)->getPdo()->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;');

                DB::connection($txDb)->transaction(function () use ($txDb, $merchantCode, $account, $amount, $orderNo, $transType, $memo) {

                    $member = DB::connection($txDb)->table('members')
                        ->where([
                            // ['merchant_code', $merchantCode],
                            ['account', $account]
                        ])->lockForUpdate()->first();

                    if ($member) {
                        $beforeBalance = $member->balance;

                        if (
                            DB::connection($txDb)
                            ->update("UPDATE members SET balance = balance + ? WHERE account = ?", [
                                floatval($amount),
                                $account
                            ])
                        ) {
                            $belongWeek = date('YW');
                            $transTable = 'transactions'."_".$belongWeek;
                            DB::connection($txDb)->table($transTable)->insert([
                                'merchant_code' => $merchantCode,
                                'account' => $account,
                                'before_balance' => $beforeBalance,
                                'transfer_amount' => $amount,
                                'balance' => bcadd($beforeBalance, $amount, 5),
                                'trans_type' => $transType,
                                'trace_id' => $orderNo,
                                'memo' => $memo,
                                'created_time' => micro_timestamp(),
                                'belong_week' => $belongWeek,
                            ]);
                        }

                    }
                });
                return (new RespData(RespState::Success, __('Success')))->toJson();
            }catch (\Throwable $th) {
                Log::info("TransferIn Fail:". $th->getMessage());
            }finally {
                RedisMutexLock::unlock($redisKey, $orderNo);
            }
        }

        return (new RespData(RespState::TransferError, __('交易失敗')))->toJson();
    }

    /**
     * 轉出
     *
     * @param [type] $amount
     * @param [type] $orderNo
     * @param string $transType
     * @param string $memo 備註
     * @return void
     */
    public static function transferOut($merchantCode, $account, $amount, $memo = null, $transType = 'transferOut') {
        $amount = floatval($amount);
        // 交易金額必須 >= 0
        if ($amount < 0) {
            return (new RespData(RespState::TransferAmountError, __('交易金額錯誤')))->toJson();
        }

        $orderNo = Self::GenOrderNo();
        $redisKey = "tx_lock_".md5($account);

        $lock = RedisMutexLock::lock($redisKey, $orderNo);
        if ($lock) {

            $txDb = strtolower(session('agent_code')."_tx");
            try {
                DB::connection($txDb)->getPdo()->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;');
                DB::connection($txDb)->beginTransaction();

                $member = DB::connection($txDb)->table('members')
                    ->where([
                        // ['merchant_code', $merchantCode],
                        ['account', $account]
                    ])->lockForUpdate()->first();

                if (empty($member)) {
                    DB::connection($txDb)->rollBack();
                    return (new RespData(RespState::MemberNotExist, __('使用者不存在')))->toJson();
                }

                if ($member->balance < $amount) {
                    DB::connection($txDb)->rollBack();
                    return (new RespData(RespState::MemberOutOfBalance, __('餘額不足')))->toJson();
                }

                $beforeBalance = $member->balance;

                if (!DB::connection($txDb)
                    ->update("UPDATE members SET balance = balance - ? WHERE account = ?", [
                        floatval($amount),
                        $account
                    ])) {
                    DB::connection($txDb)->rollBack();
                    Log::info("TransferOut-交易失敗");
                }

                // 交易紀錄
                $belongWeek = date('YW');
                $transTable = 'transactions'."_".$belongWeek;
                $log = DB::connection($txDb)->table($transTable)->insert([
                    'merchant_code' => $merchantCode,
                    'account' => $account,
                    'before_balance' => $beforeBalance,
                    'transfer_amount' => $amount,
                    'balance' =>  bcsub($beforeBalance, $amount, 5),
                    'trans_type' => $transType,
                    'trace_id' => $orderNo,
                    'memo' => $memo,
                    'created_time' => micro_timestamp(),
                    'belong_week' => $belongWeek,
                ]);

                if (!$log) {
                    DB::connection($txDb)->rollBack();
                    Log::info("TransferOut-交易紀錄寫入失敗");
                }

                DB::connection($txDb)->commit();
                return (new RespData(RespState::Success, __('Success')))->toJson();
            }catch (\Throwable $th) {
                DB::connection($txDb)->rollBack();
                Log::info("TransferOut Fail:". $th->getMessage());
            }finally {
                RedisMutexLock::unlock($redisKey, $orderNo);
            }
        }

        return (new RespData(RespState::TransferError, __('交易失敗')))->toJson();
    }

    /**
     * 會員轉點
     *
     * @param $formAccount
     * @param $toAccount
     * @param $amount
     * @param null $memo
     * @return array
     */
    public static function memberTransaction($formAccount, $toAccount, $amount, $memo = null)
    {
        $amount = floatval($amount);
        // 交易金額必須 >= 0
        if ($amount < 0) {
            return (new RespData(RespState::TransferAmountError, __('交易金額錯誤')))->toArray();
        }

        // $orderNo = md5(Str::uuid()->toString());
        $orderNo = self::GenOrderNo();

        $prefix = "tx_lock_";
        $fromRedisKey = $prefix.md5($formAccount);
        $toRedisKey = $prefix.md5($toAccount);
        $lock = RedisMutexLock::lock($fromRedisKey, $orderNo) && RedisMutexLock::lock($toRedisKey, $orderNo);

        if ($lock) {

            $txDb = strtolower(session('agent_code')."_tx");
            try {
                DB::connection($txDb)->getPdo()->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;');
                DB::connection($txDb)->beginTransaction();

                $fromMember = DB::connection($txDb)->table('members')
                    ->where([
                        ['account', $formAccount]
                    ])->lockForUpdate()->first();

                if ($fromMember->balance < $amount) {
                    DB::connection($txDb)->rollBack();
                    return (new RespData(RespState::MemberOutOfBalance, __('餘額不足')))->toArray();
                }

                $fromBeforeBalance = $fromMember->balance;

                if (!DB::connection($txDb)
                    ->update("UPDATE members SET balance = balance - ? WHERE account = ?", [
                        floatval($amount),
                        $formAccount
                    ])) {
                    DB::connection($txDb)->rollBack();
                    Log::info("TransferOut-交易失敗");
                }

                // 交易紀錄
                $belongWeek = date('YW');
                $transTable = 'transactions'."_".$belongWeek;

                $transLog = [
                    'merchant_code' => $fromMember->merchant_code,
                    'account' => $formAccount,
                    'before_balance' => $fromBeforeBalance,
                    'transfer_amount' => $amount,
                    'balance' =>  bcsub($fromBeforeBalance, $amount, 5),
                    'trans_type' => "transferOut",
                    'trace_id' => $orderNo."-OUT",
                    'memo' => $memo,
                    'created_time' => micro_timestamp(),
                    'belong_week' => $belongWeek,
                ];
                $log = DB::connection($txDb)->table($transTable)->insert($transLog);

                if (!$log) {
                    DB::connection($txDb)->rollBack();
                    Log::info("TransferOut-交易紀錄寫入失敗");
                }

                $toMember = DB::connection($txDb)->table('members')->where([
                    ['account', $toAccount]
                ])->lockForUpdate()->first();

                $toBeforeBalance = $toMember->balance;


                if (!DB::connection($txDb)
                    ->update("UPDATE members SET balance = balance + ? WHERE account = ?", [
                        floatval($amount),
                        $toAccount
                    ])) {
                    DB::connection($txDb)->rollBack();
                    Log::info("TransferIn-交易失敗");
                }

                // 交易紀錄
                $toLog = DB::connection($txDb)->table($transTable)->insert([
                    'merchant_code' => $toMember->merchant_code,
                    'account' => $toAccount,
                    'before_balance' => $toBeforeBalance,
                    'transfer_amount' => $amount,
                    'balance' =>  bcadd($toBeforeBalance, $amount, 5),
                    'trans_type' => "transferIn",
                    'trace_id' => $orderNo."-IN",
                    'memo' => $memo,
                    'created_time' => micro_timestamp(),
                    'belong_week' => $belongWeek,
                ]);

                if (!$toLog) {
                    DB::connection($txDb)->rollBack();
                    Log::info("TransferIn-交易紀錄寫入失敗");
                }

                DB::connection($txDb)->commit();
                return (new RespData(RespState::Success, __('Success'), $transLog))->toArray();
            }catch (\Throwable $th) {
                DB::connection($txDb)->rollBack();
                Log::info("TransferOut Fail:". $th->getMessage());
            }finally {
                RedisMutexLock::unlock($fromRedisKey, $orderNo);
                RedisMutexLock::unlock($toRedisKey, $orderNo);
            }
        }

        return (new RespData(RespState::TransferError, __('交易失敗')))->toArray();

    }
    /**
     * 訂單號
     *
     * @param [type] $prefix
     * @return string
     */
    public static function GenOrderNo() {
        $prefix = session('operator_code') ?? 'TX';
        $prefix = strtoupper($prefix);
        $time = intval(microtime(true) * 1000);

        $suffix = Str::random(10);
        return "$prefix{$time}$suffix";
    }

}
