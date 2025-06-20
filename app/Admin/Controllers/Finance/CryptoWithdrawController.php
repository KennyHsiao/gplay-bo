<?php

namespace App\Admin\Controllers\Finance;

use App\Admin\Actions\Finance\CryptoWithdrawComplete;
use App\Admin\Actions\Finance\CryptoWithdrawVerify;
use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Admin\Controllers\Traits\Common;
use App\Models\Finance\CryptoWithdraw;

/**
 * 區塊鏈提款
 */
class CryptoWithdrawController extends AdminController
{
    use Common;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '區塊鏈提款';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CryptoWithdraw());
        $grid->column('trace_id', __('單號'));
        $grid->column('slug', __('幣別'));
        $grid->column('account', __('玩家帳號'))->totalRow('合計');
        $grid->column('wallet', __('錢包地址'))->copyable();
        $grid->column('transfer_amount', __('交易金額'))->totalRow();
        $grid->column('tx_id', __('交易序號'))->copyable();
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
            $batch->add(new CryptoWithdrawVerify());
            $batch->add(new CryptoWithdrawComplete());
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
        $show = new Show(CryptoWithdraw::findOrFail($id));

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

        $form = new Form(new CryptoWithdraw);
        $form->hidden('id');
        return $form;
    }
}
