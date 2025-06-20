<?php

namespace App\Admin\Controllers\Merchant;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Models\Merchant\GameType;
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
        $grid->column('c_flag', __('自訂'))->label([
            '0' => 'default',
            '1' => 'warning'
        ]);
        $grid->column('status', __('狀態'))->switch(static::formSwitchLocale('public'));
        $grid->column('sort_order', __('排序'))->editable();
        $grid->model()->where('merchant_code', session('merchant_code'))->orderBy('sort_order')->orderBy('code');
        // filter
        $grid->disableFilter();
        $grid->disableCreateButton();
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
        $form->hidden('merchant_code');
        $form->display('code', __('代碼'));
        $form->keyValue('name', __('名稱'))->rules('required')->value(static::supportLang());
        $form->switch('status', __('狀態'))->states(static::formSwitchLocale('public'));
        $form->switch('c_flag', __('自訂'))->states(static::formSwitchLocale('open'));
        $form->number('sort_order', __('排序'));
        $form->image('bg', __('背景'))->help(__('建議尺寸: 163 x 152'))->uniqueName()->removable();
        //保存前回调
        return $form;
    }
}
