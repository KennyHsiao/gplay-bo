<?php

namespace App\Admin\Controllers\System;

use App\Admin\Actions\DatabaseSetting\ActionInitDatabase;
use App\Admin\Actions\DatabaseSetting\ActionInitDatabaseReport;
use App\Admin\Actions\System\DatabaseSettingCopy;
use App\Admin\Controllers\AdminController;
use App\Admin\Controllers\Traits\Common;
use App\Helpers\MongoTool;
use App\Helpers\GlobalParam;
use App\Models\Platform\Company;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use Xn\Admin\Facades\Admin;
use App\Models\System\Database;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DatabaseSettingController extends AdminController
{
    use Common;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '資料庫配置';

    static function slugs() {
        return [
            'db' => __('交易/報表'),
            'agent' => __('代理'),
            'alert' => __('告警'),
        ];
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $company = Company::select(DB::raw("CONCAT('(',code,')',name) AS name"),'code')->where('type', 'agent')->pluck('name', 'code')->toArray();
        $grid = new Grid(new Database);
        $grid->column('agent_code', __('代理'))->display(function($data)use($company){
            return $company[$data]??$data;
        })->sortable();
        $grid->column('slug', __('類別'))->display(function($v){
            return self::slugs()[$v];
        });
        $grid->column('title', __('標題'))->sortable();
        $grid->column('enabled', __('開啟'))->switch(static::formSwitchLocale('public'));
        $grid->model()->orderBy('agent_code')->orderBy('slug', 'desc');
        $grid->disableRowSelector(false);
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->equal('agent_code', __('代理'))
                ->select(Company::select(DB::raw("CONCAT('(',code,')',name) AS name"),'code')->where('type', 'agent')->pluck('name', 'code'));
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('slug', __('類別'))->select(self::slugs());
            });
        });
        //
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete(false);
            });
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new DatabaseSettingCopy());
        });
        // actions
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();

            if (!Admin::user()->isRole('administrator')) {
                $actions->disableDelete();
            }
            //
            if ($actions->row->slug === 'db') {
                $actions->prepend(
                    new ActionInitDatabase($actions->row->agent_code)
                );
            }

            if ($actions->row->slug === 'db') {
                $actions->prepend(
                    new ActionInitDatabaseReport($actions->row->agent_code)
                );
            }

        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Database::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $js = <<<EOF
        (function(){
            $('[name="slug"]').on('change', function(){
                type = $('select[name="slug"]').val();
                if (type == 'tx') {
                    window['editor_setting'].aceEditor.setValue('{"ip":"192.168.10.101","port":6432,"user":"postgres","password":""}');
                } else {
                    window['editor_setting'].aceEditor.setValue('{"ip":"192.168.30.41:27017,192.168.30.42:27017,192.168.30.43:27017","rs":"beta-db"}');
                }
            });
        })()
        EOF;
        // Admin::script($js);
        $form = new Form(new Database);
        $form->hidden('id');
        $form->select('agent_code', __('代理'))->options(Company::select(DB::raw("CONCAT('(',code,')',name) AS name"),'code')->where('type', 'agent')->pluck('name', 'code'))->rules('required');
        $form->select('slug', __('類別'))->options(self::slugs())->rules('required');
        $form->text('title', __('標題'))->rules('required');
        // $form->json('setting', __('設定'))->rules('required');
        $form->json('tx_db', __('交易資料庫'))->default('{"ip":"192.168.10.101","port":6432,"user":"postgres","password":""}')->rules('required');
        $form->json('rep_db', __('報表資料庫'))->default('{"ip":"192.168.30.41:27017,192.168.30.42:27017,192.168.30.43:27017","rs":"beta-db"}')->rules('required');
        $form->text('db_name', __('DB名'))->rules('required');
        $form->json('mongo_index', __('Mongo索引'));
        $form->switch('enabled', __('開啟'))->states(static::formSwitchLocale('public'));
        return $form;
    }

    /**
     * 初始化資料庫
     *
     * @param Request $request
     * @return void
     */
    public function initDatabase(Request $request) {
        $model = Database::where([
            'agent_code' => $request->input('code'),
            'slug' => 'db'
        ])->first();

        $dbName = strtolower("{$model->agent_code}_tx");
        $connName = GlobalParam::CreateTxDbConfig($model->tx_db);
        // 建立DB
        $messages = [];
        try {
            DB::connection($connName)->statement("create database {$dbName} template db_template;");
            sleep(3);
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
        }
        // 建立Partition
        try {
            $connName = GlobalParam::CreateTxDbConfig($model->tx_db, $dbName);
            $year = date('Y');
            DB::connection($connName)->statement("SELECT create_partition('{$year}');");
            if (intval(date("md")) >= 1128 ) {
                // 排程創建partition 在 11/28 如果商戶創建在11/28後就必須幫該商戶建立來年的partitions
                $nextYear = $year + 1;
                DB::connection($connName)->statement("SELECT create_partition('{$nextYear}');");
            }
            $year = date('Y');
            DB::connection($connName)->statement("SELECT create_partition('{$year}');");
            if (intval(date("md")) >= 1128 ) {
                // 排程創建partition 在 11/28 如果商戶創建在11/28後就必須幫該商戶建立來年的partitions
                $nextYear = $year + 1;
                DB::connection($connName)->statement("SELECT create_partition('{$nextYear}');");
            }
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
        }

        if (empty($messages)){
            $messages[] = __('admin.succeeded');
        }

        return response()->json([
            'data'=>[
                'text' => implode('\r\n', $messages)
            ]
        ]);
    }

    /**
     * 初始化報表
     *
     * @param Request $request
     * @return void
     */
    public function initReportDatabase(Request $request) {
        // MongoTool
        try {
            // 建立MongoDB
            MongoTool::CreateIndexs($request->input('code'));
            // 建立MongoDB View
            MongoTool::CreateMerchantView($request->input('code'));
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
        }

        if (empty($messages)){
            $messages[] = __('admin.succeeded');
        }

        return response()->json([
            'data'=>[
                'text' => implode('\r\n', $messages)
            ]
        ]);
    }
}
