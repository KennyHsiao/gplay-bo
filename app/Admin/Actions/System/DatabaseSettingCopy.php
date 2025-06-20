<?php

namespace App\Admin\Actions\System;

use App\Models\Platform\Company;
use App\Models\System\Database;
use Exception;
use Xn\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseSettingCopy extends BatchAction
{
    protected $selector = '.database-setting-copy';

    public function handle(Collection $collection, Request $request)
    {
        $newAgentCode = $request->input('agent_code');

        foreach ($collection as $model) {
            try {
                unset($model->id);
                $new = new Database($model->toArray());
                $new->agent_code = $newAgentCode;
                $new->save();
            } catch (Exception $e) {
                \Log::info($e->getMessage());
            }
        }

        return $this->response()->success(__('admin.succeeded'))->refresh();
    }

    public function form()
    {
        $this->select('agent_code', __('代理'))
            ->options(Company::select(DB::raw("CONCAT('(',code,')',name) AS name"),'code')->where('type', 'agent')->pluck('name', 'code'))
            ->rules('required');
    }

    public function html()
    {
        $label = __('複製配置');
        return "<a class='database-setting-copy btn btn-sm btn-success'><i class='fa fa-info-circle'></i>{$label}</a>";
    }

}
