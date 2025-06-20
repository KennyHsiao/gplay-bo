<?php

namespace App\Admin\Controllers\GameManage;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Models\GameManage\GameTag;
use App\Admin\Controllers\Traits\Common;
/**
 * 遊戲標籤
 */
class GameTagController extends AdminController
{
    use Common;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '遊戲標籤';


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GameTag);

        $grid->column('code', __('代碼'));
        $grid->column('name', __('名稱'))->display(function($v){
            return $v[session('locale')]??$v['en'];
        });
        // $grid->column('updated_at', __('Updated at'));
        // filter
        $grid->disableFilter();
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
        $show = new Show(GameTag::findOrFail($id));

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

        $form = new Form(new GameTag);
        $form->hidden('id');
        $form->text('code', __('代碼'))->rules('required|alpha_num|unique:gm_tags,code,'.request()->input('id',0).',id')->attribute('maxlength', 8);
        $form->keyValue('name', __('名稱'))->rules('required')->value(static::supportLang());
        //保存前回调
        $form->saving(function (Form $form) {
            $form->code = strtolower($form->code);
        });
        return $form;
    }
}
