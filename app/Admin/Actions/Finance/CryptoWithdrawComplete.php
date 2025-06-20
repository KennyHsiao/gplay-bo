<?php

namespace App\Admin\Actions\Finance;

use Illuminate\Database\Eloquent\Collection;
use Xn\Admin\Actions\BatchAction;

class CryptoWithdrawComplete extends BatchAction
{
    public $name = '批次完成';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            $model->status = '3';
            $model->save();
        }

        return $this->response()->success(__('審核完成'))->refresh();
    }

}
