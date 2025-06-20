<?php

namespace App\Admin\Controllers\Player;

use App\Admin\Controllers\Traits\Common;
use App\Models\Player\Transaction;
use App\Models\Platform\Company;
use Xn\Admin\Controllers\AdminController;
use Xn\Admin\Facades\Admin;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use Xn\FilterDateRangePicker\TimestampRange;

class TransactionController extends AdminController
{
    use Common;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '交易紀錄';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Transaction());
        $grid->model()->orderByDesc('created_time');

        $grid->column('merchant_code', __('商戶號'))->hide();
        $grid->column('member.picture_url', __('頭像'))->width('100')->image(null, 50);
        $grid->column('member.display_name', __('顯示名稱'));
        $grid->column('trace_id', __('交易單號'));
        $grid->column('trans_type', __('交易類型'));
        $grid->column('account', __('帳號'));
        $grid->column('before_balance', __('交易前餘額'));
        $grid->column('transfer_amount', __('交易額'));
        $grid->column('balance', __('餘額'));


        $grid->column('created_time', __('交易時間'));
        $grid->column('memo', __('備註'))->hide();

        $grid->column('merchant_trace_id', __('商戶單號'))->hide();
        // $grid->column('bet_id', __('Bet id'))->hide();
        $grid->model()->where('merchant_code', session('merchant_code'));

        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->equal('account', __('帳號'));
                $filter->use((new TimestampRange('created_time', __('交易日期')))->timezone(session('timezone')))
                    ->daterangepicker('default', ['autoUpdateInput'=>false]);
                $filter->equal('merchant_trace_id', __('商戶訂單號'));
            });
            $filter->column(1/2, function ($filter) {
                $filter->in('trans_type', __('交易類型'))->multipleSelect(TransactionController::formTransType());
                $filter->equal('trace_id', __('交易單號'));
                $filter->equal('bet_id', __('注單號'));
            });

        });

        // 预设不显示资料
        if (empty($_GET)||(count($_GET) ==1 && isset($_GET['_pjax']))) {
            $grid->model()->where("trace_id", "null");
        }

        if (empty($_GET['bet_id']) && empty($_GET['trace_id'])) {
            if (empty($_GET['created_time'])) {
                $grid->model()->where("trace_id", "null");
                admin_toastr(
                    __('validation.required', ['attribute' => __('交易日期')]), 'error', [
                    "positionClass" => "toast-top-center",
                    "preventDuplicates" => 1
                ]);
            }
        }

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Transaction::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('account', __('Account'));
        $show->field('before_balance', __('Before balance'));
        $show->field('transfer_amount', __('Transfer amount'));
        $show->field('balance', __('Balance'));
        $show->field('trans_type', __('Trans type'));
        $show->field('trace_id', __('Trace id'));
        $show->field('created_time', __('Created time'));
        $show->field('memo', __('Memo'));
        $show->field('merchant_code', __('Merchant code'));
        $show->field('merchant_trace_id', __('Merchant trace id'));
        $show->field('bet_id', __('Bet id'));
        $show->field('belong_week', __('Belong week'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Transaction());

        $form->text('account', __('Account'));
        $form->decimal('before_balance', __('Before balance'));
        $form->decimal('transfer_amount', __('Transfer amount'));
        $form->decimal('balance', __('Balance'));
        $form->text('trans_type', __('Trans type'));
        $form->text('trace_id', __('Trace id'));
        $form->number('created_time', __('Created time'));
        $form->text('memo', __('Memo'));
        $form->text('merchant_code', __('Merchant code'));
        $form->text('merchant_trace_id', __('Merchant trace id'));
        $form->text('bet_id', __('Bet id'));
        $form->text('belong_week', __('Belong week'));

        return $form;
    }
}
