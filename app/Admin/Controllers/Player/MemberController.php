<?php

namespace App\Admin\Controllers\Player;

use App\Admin\Actions\Member\AuthCodeReset;
use App\Admin\Actions\Member\Deposit;
use App\Admin\Actions\Member\Withdraw;
use App\Admin\Controllers\AdminController;
use App\Admin\Controllers\Traits\Common;
use App\Models\Platform\Company;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Facades\Admin;
use App\Models\Player\Member;

class MemberController extends AdminController
{

    use Common;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '會員管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Member());

        if (Admin::user()->isRole('administrator')) {
            $grid->model()->orderByDesc('updated_time');
        }
        $grid->column('picture_url', __('頭像'))->width('100')->image(null, 50);
        $grid->column('account', __('帳號'));
        $grid->column('display_name', __('顯示名稱'));
        $grid->column('balance', '錢包')->display(function($v){
            $balance = __('餘額') . " : " . floatval($this->balance) . "<br>";
            $balancefz = __('凍結餘額') . " : " . floatval($this->balance_frozen) . "<br>";
            return $balance . $balancefz;
        })->sortable();
        $grid->column('ton_wallet', '錢包地址')->hide();
        // $grid->column('daily_max_withdraw','每日下分')->display(function($v){
        //     return floatval($v);
        // });

        $grid->column('updated_time', __('更新日期'))->sortable();
        $grid->column('status', '狀態')->switch(static::formSwitchLocale('status'));

        $grid->model()->where('merchant_code', session('merchant_code'));

        $grid->disableCreateButton();
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('account', '帳號');
            // $filter->selectGroup('merchant_code', __('商戶代碼'))->options(Company::where('type', 'agent')->with('merchants')->get())->value('code');

        });
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();

            if (Admin::user()->isRole('administrator')) {
                $actions->append(
                    new Deposit($actions->row->merchant_code, $actions->row->account)
                );
                $actions->append(
                    new Withdraw($actions->row->merchant_code, $actions->row->account)
                );
            }
            $actions->append(
                new AuthCodeReset($actions->row->merchant_code, $actions->row->account)
            );
        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Member::class, function (Form $form) {
            ### disable button
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
                $tools->disableView();
            });
            ###
            $form->display('id', 'ID');
            $form->hidden('id');
            $form->switch('status', '狀態')->states(static::formSwitchLocale('status'));
        });
    }
}
