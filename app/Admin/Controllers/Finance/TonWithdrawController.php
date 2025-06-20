<?php

namespace App\Admin\Controllers\Finance;

use App\Admin\Actions\Finance\TonWithdrawComplete;
use App\Admin\Actions\Finance\TonWithdrawVerify;
use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Admin\Controllers\Traits\Common;
use App\Models\Finance\TonWithdraw;

/**
 * Ton提款
 */
class TonWithdrawController extends AdminController
{
    use Common;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Ton提款';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TonWithdraw());
        $grid->column('trace_id', __('單號'));
        $grid->column('account', __('玩家帳號'))->totalRow('合計');
        $grid->column('ton_wallet', __('錢包地址'))->copyable();
        $grid->column('transfer_amount', __('交易金額'))->totalRow();
        $grid->column('status', __('狀態'))->loading([2], [
            0 => __('待審核'),
            1 => __('準備出款'),
            2 => __('確認中'),
            3 => __('已完成'),
        ])->label([
            0 => 'default',
            1 => 'default',
            2 => 'warning',
            3 => 'success',
        ]);
        ;
        $grid->column('memo', __('備註'));
        $grid->column('created_time', __('建立時間'));
        $grid->model()->orderBy('id', 'desc');
        // filter
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableFilter();

        $grid->disableRowSelector(false);
        $grid->batchActions(function ($batch) {
            $batch->add(new TonWithdrawVerify());
            $batch->add(new TonWithdrawComplete());
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
        $show = new Show(TonWithdraw::findOrFail($id));

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

        $form = new Form(new TonWithdraw);
        $form->hidden('id');
        return $form;
    }
}
