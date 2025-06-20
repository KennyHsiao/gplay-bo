<?php

namespace App\Admin\Controllers\Finance;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Models\Finance\Adjustment;
use App\Admin\Controllers\Traits\Common;
use Xn\Admin\Auth\Database\Administrator;
use Xn\Admin\Facades\Admin;
use Xn\FilterDateRangePicker\TimestampRange;
use Illuminate\Support\MessageBag;
use Xn\Admin\Helper\DateTransform;

/**
 * 玩家资金调整
 */
class PlayerAdjustmentController extends AdminController
{
    use Common;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '玩家資金調整';


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Adjustment);
        $grid->column('trace_id', __('單號'));
        $grid->column('account', __('玩家帳號'))->totalRow('合計');
        $grid->column('before_balance', __('交易前餘額'))->display(function($v){
            return number_format($v);
        });
        $grid->column('transfer_amount', __('交易金額'))->display(function($v){
            return number_format($v);
        })->totalRow();
        $grid->column('balance', __('餘額'))->display(function($v){
            return number_format($v);
        });
        $grid->column('memo', __('備註'));
        $grid->column('created_time', __('建立時間'));
        $grid->column('created_by', __('建立人員'));
        $grid->model()->orderBy('created_time', 'desc');
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->equal('account', __('玩家帳號'));
                $filter->use((new TimestampRange('created_time', __('建立時間')))->timezone(session('timezone')))
                    ->daterangepicker('default', ['autoUpdateInput'=>false]);
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('trace_id', __('單號'));
            });
        });
        $grid->disableActions();
        // $grid->disableCreateButton();
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
        $show = new Show(Adjustment::findOrFail($id));

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

        $form = new Form(new Adjustment);
        $form->hidden('trace_id')->default("empty");
        $form->hidden('merchant_code')->default(session('merchant_code'));
        $form->select('account', __('玩家帳號'))->ajax(route('api.get-player'))->rules('required');
        $form->text('transfer_amount', __('交易金額'))->inputmask([
            'alias' => 'numeric',
            'allowMinus' => true,
            'digits' => 4,
            'max' => 9999999.9999
        ])->rules('required');
        $form->text('memo', __('備註'))->rules('required')->attribute('maxlength', 64);
        $form->hidden('created_by')->default(Admin::user()->username);
        // $form->ignore(['admin_secret_key']);
        // 保存前回调
        $form->saving(function (Form $form) {
            if ($form->admin_secret_key != Admin::user()->admin_secret_key) {
                $error = new MessageBag([
                    'title' => __('交易密鑰錯誤'),
                ]);
                return back()->with(compact('error'));
            }
            // 通知LINENotify
            $date = DateTransform::CurrDatetime(session('timezone'));
            $message = <<<EOF
                玩家資金調整
                帳號：$form->account
                金額：$form->transfer_amount
                備註：$form->memo
                人員：$form->created_by
                時間：$date
            EOF;
            $linebot = Administrator::where('username', 'linebot')->first();
            $this->sendNotify($linebot, $message, 0);
        });

        $form->confirmAuth(trans('admin.password_confirmation'), route('api.auth-confirm-x'));

        return $form;
    }

    /**
     * LINE Notify
     *
     * @param [type] $bot
     * @param [type] $message
     * @param integer $stickerId
     * @return void
     */
    private function sendNotify($bot, $message, $stickerId) {
        if ($bot && isset($bot->line_notify_token)) {
            $this->sendLineNotify($bot->line_notify_token, $message, $stickerId);
        }
    }
}
