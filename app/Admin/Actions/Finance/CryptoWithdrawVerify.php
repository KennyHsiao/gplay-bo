<?php

namespace App\Admin\Actions\Finance;

use Illuminate\Database\Eloquent\Collection;
use Xn\Admin\Actions\BatchAction;

class CryptoWithdrawVerify extends BatchAction
{
    public $name = '批次審核';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            if ($model->status == '0') {
                $model->status = '1';
                $model->save();
            }
        }

        return $this->response()->success(__('審核完成'))->refresh();
    }

}
