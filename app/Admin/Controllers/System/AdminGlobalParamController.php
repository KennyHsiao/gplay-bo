<?php

namespace App\Admin\Controllers\System;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use App\Admin\Controllers\Traits\Common;
use App\Models\System\AdminGlobalParam;

class AdminGlobalParamController extends AdminController
{
    use Common;

    protected $param_types = [
        'string' => '文字',
        'textarea' => '文字方塊',
        'int' => '數值',
        'radio' => '單選題',
        'checkbox' => '複選題',
        'select' => '下拉選單',
        'image' => '圖片'
    ];

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '全域參數';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AdminGlobalParam());

        $grid->column('type', __('全域參數'))->sortable();
        $grid->column('param_name', __('參數名稱'))->editable();
        $grid->column('param_slug', __('參數識別'));
        $grid->column('param_type', __('參數類型'))->editable('select', $this->param_types);
        $grid->column('param_required', __('必填選項'))->switch();
        $grid->column('param_memo', __('備註'))->editable();
        $grid->column('seq', __('排序'))->editable();

        $grid->model()->orderBy('seq')->orderBy('type');
        // actions
        $grid->actions(function ($actions) {
            $actions->disableView();
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
        $form = new Form(new AdminGlobalParam);

        $form->hidden('id');
        $form->text('type', __('類別'))->rules('required|max:20')->attribute('maxlength', 20);

        $form->select('param_type', __('參數類型'))->options($this->param_types)->rules('required');

        $form->text('param_name', __('參數名稱'))->rules(function ($form) {
            return 'required|max:30|unique:admin_global_params,param_name,'.request()->input('id', 0).',id';
        })->attribute('maxlength', 30);

        $form->text('param_slug', __('參數識別'))->rules(function ($form) {
            return 'required|max:30|unique:admin_global_params,param_slug,'.request()->input('id', 0).',id';
        })->attribute('maxlength', 30);

        $form->text('param_default', __('參數初始值'))->rules('max:255')->attribute('maxlength', 255);

        $form->switch('param_required', __('必填選項'))->options(static::$switch_open);

        $form->textarea('param_values', __('選項列表'))->rules('required_if:param_type,checkbox,select,radio')
            ->attribute('maxlength', 300)->help(__('請使用斷行分隔選項'));

        $form->text('memo', __('備註'))->rules(function ($form) {
            return 'max:100';
        })->attribute('maxlength', 100);

        $form->number('seq', __('排序'))->default(0);

        return $form;
    }
}
