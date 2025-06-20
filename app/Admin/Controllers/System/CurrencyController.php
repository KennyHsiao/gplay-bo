<?php

namespace App\Admin\Controllers\System;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Models\System\Currency;

class CurrencyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '幣別管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Currency);

        $grid->column('code', __('代碼'))->sortable();
        $grid->column('name', __('名稱'))->sortable();
        $grid->column('c_rate', __('比率'))->editable();
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->like('code', __('代碼'));
            });
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
        $show = new Show(Currency::findOrFail($id));

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
        $form = new Form(new Currency);
        $form->hidden('id');
        $form->text('code', __('代碼'))->rules('required|unique:sys_currencies,code,'.request()->input('id',0).',id');
        $form->text('name', __('名稱'))->rules('required');
        $form->text('c_rate', __('比率'))->rules('required')->default(1);
        $form->saving(function (Form $form) {
            if ($form->code) {
                $form->code = strtoupper($form->code);
            }
        });

        return $form;
    }
}
