<?php

namespace App\Admin\Controllers\GameManage;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Models\GameManage\GameType;
use App\Admin\Controllers\Traits\Common;
/**
 * 遊戲類型
 */
class GameTypeController extends AdminController
{
    use Common;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '遊戲類型';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GameType);
        $grid->column('bg', __('背景'))->width(200)->image(null, 100);
        $grid->column('code', __('代碼'));
        $grid->column('name', __('名稱'))->display(function($v){
            return $v[session('locale')]??$v['en'];
        });
        $grid->column('status', __('狀態'))->switch(static::formSwitchLocale('public'));
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
        $show = new Show(GameType::findOrFail($id));

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

        $form = new Form(new GameType);
        $form->hidden('id');
        $form->text('code', __('代碼'))->rules('required|min:2|alpha_num|unique:gm_types,code,'.request()->input('id',0).',id')->attribute('maxlength', 8);
        $form->keyValue('name', __('名稱'))->rules('required')->value(static::supportLang());
        $form->switch('status', __('狀態'))->states(static::formSwitchLocale('public'));
        $form->image('bg', __('背景'))->help(__('建議尺寸: 163 x 152'))->uniqueName()->removable();
        //保存前回调
        $form->saving(function (Form $form) {
            $form->code = strtoupper($form->code);
        });
        return $form;
    }
}
