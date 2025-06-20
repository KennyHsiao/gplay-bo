<?php

namespace App\Admin\Controllers\Merchant;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Admin\Controllers\Traits\Common;
use App\Models\GameManage\GameVendor as GameManageGameVendor;
use App\Models\Merchant\GameVendor;
use App\Models\Platform\Company;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Facades\Admin;

/**
 * 遊戲商
 */
class GameVendorController extends AdminController
{
    use Common;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '遊戲商管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GameVendor);
        $grid->column('pic', __('圖片'))->image(null, 100);
        $grid->column('vendor', __('遊戲商'))->display(function($v){
            return $v['name'][session('locale')]."({$v['code']})";
        });
        $grid->column('status', __('狀態'))->switch(static::formSwitchLocale('public'));
        $grid->column('sort_order', __('排序'))->editable();
        $grid->model()->where('merchant_code', session('merchant_code'))->orderBy('sort_order')->orderBy('vendor_code');
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->equal('merchant_code', __('商戶'))->select(Company::whereHas('merchant')->pluckKV());
                $filter->equal('status', __('狀態'))->select(static::switchPublic());
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('vendor_code', __('遊戲商'))->select(GameManageGameVendor::pluckKV());
            });

        });
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();
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
        $show = new Show(GameVendor::findOrFail($id));

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
        $vendorSettingAPI = route("api.get-vendor-setting");
        $js = <<<EOF
        (function(){
            $('[name="vendor_code"]').off('change');
            $('[name="vendor_code"]').on('change', function(){
                $.get('$vendorSettingAPI?code='+$(this).val(), function(data){
                    window['editor_params'].aceEditor.setValue(JSON.stringify(data.setting));
                });
            });
        })()
        EOF;
        Admin::script($js);
        $form = new Form(new GameVendor);
        $form->hidden('id');
        if ($form->isCreating()) {
            $form->select('merchant_code', __('商戶'))->options(Company::whereHas('merchant')->pluckKV())->rules('required');
            $form->select('vendor_code', __('遊戲商'))->options(GameManageGameVendor::pluckKV())->rules('required|unique:mc_vendors,vendor_code,'.request()->input('id',0).',id,merchant_code,'.request()->input('merchant_code',''));
        } else {
            $form->hidden('merchant_code');
            $form->hidden('vendor_code');
            $form->display('merchant_code', __('商戶'))->customFormat(function($v){
                $comp = Company::whereHas('merchant')->pluckKV();
                return $comp[$v];
            });
            $form->display('vendor_code', __('遊戲商'))->customFormat(function($v){
                $vendor = GameManageGameVendor::pluckKV();
                return $vendor[$v];
            });
        }


        // $form->json('params', __('請求參數'));
        $form->image('pic', __('圖片'))->rules('dimensions:ratio=267/130')->uniqueName()->help('size=267x130')->removable();
        $form->json('params', __('請求參數'));
        $form->switch('status', __('狀態'))->states(static::formSwitchLocale('public'));
        $form->number('sort_order', __('排序'))->default(0);
        //保存前回调
        $form->saving(function (Form $form) {
            if ($form->isCreating()) {
                $form->merchant_code = strtoupper($form->merchant_code);
            }
        });
        return $form;
    }
}
