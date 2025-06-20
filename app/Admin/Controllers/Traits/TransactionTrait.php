<?php

namespace App\Admin\Controllers\Traits;

use App\Models\Player\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Xn\Admin\Facades\Admin;

trait TransactionTrait
{
    public function boDeposit(Request $request) {

        $validator = Validator::make($request->all(), [
            'account_id'    => 'required',
            'transtype'     => 'required|in:boDeposit',
            'amount'        => 'required|min:1',
            'memo'          => 'required',
            'key'           => 'required',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => '0',
                'errors' => $validator->errors()->all()
            ], 200);
        }
        # 檢查錢包是否存在
        $walletCode = 'player_balance';
        $transType = $request->input('transtype');
        $amount = floatval($request->input('amount'));
        $member = Member::find($request->input('account_id'));
        $memo = $request->input('memo');
        $key = $request->input('key');
        $memberAccount = $member->account;

        # 檢查金鑰是否正確
        $secret_key = Admin::user()->secret_key;
        if (!Hash::check($key, $secret_key)) {
            return response()->json([
                'status' => '0',
                'data' => []
            ], 200);
        }

        // locale-message
        $trans = [
            'account'                   => __('members.account'),
            'deposit_before_balance'    => __('wallet.charge.deposit_before_balance'),
            'deposit_amount'            => __('wallet.charge.deposit_amount'),
            'balance'                   => __('wallet.charge.balance'),
            'not_enough'                => __('wallet.charge.not_enough')
        ];

        $orderNo = TransactionRepository::GenOrderNo();
        $transaction = new TransactionRepository($memberAccount, $walletCode);
        try {
            #$result = $transaction->transferIn($amount, $orderNo, 'boDeposit', $memo);
            $result = $transaction->transferIn($amount, $orderNo, 'boReDeposit', $memo); //因後台上分通常場景為 補分 , 為避免被計入累計存款 , 因此 2021-09-29 新增後台補分之交易類型
            $message = <<< EOF
                {$trans['account']} : {$result['account']}
                {$trans['deposit_before_balance']} :{$result['before_balance']}
                {$trans['deposit_amount']} : {$result['amount']}
                {$trans['balance']} : {$result['balance']}
EOF;


            #更新提款所需流水
            #\App\Helper\RecordStatTool::updateMemberWithdrawalRebateRequirement(session('operator_code'), $memberAccount, floatval($result['amount'])*1, $orderNo);

            return Response::success([
                'text' => $message
            ]);
        } catch (\Exception $e) {
            return Response::success([
                'text' => $e->getMessage()
            ]);
        }
    }

}
