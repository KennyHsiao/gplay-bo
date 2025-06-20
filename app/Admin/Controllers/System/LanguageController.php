<?php

namespace App\Admin\Controllers\System;

use App\Admin\Controllers\AdminController;
use App\Models\Player\Transaction;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Models\System\Language;
use Xn\Dx\DxDataGrid;

class LanguageController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '語系管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Language);
        $grid->column('code', __('代碼'));
        $grid->column('name', __('名稱'));
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
        $show = new Show(Language::findOrFail($id));

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
        $form = new Form(new Language);
        $form->hidden('id');
        $form->text('code', __('代碼'))->rules('required|unique:sys_languages,code,'.request()->input('id',0).',id');
        $form->text('name', __('名稱'))->rules('required');
        $form->saving(function (Form $form) {
            if ($form->code) {
                $form->code = strtolower($form->code);
            }
        });

        return $form;
    }
}
