<?php

namespace App\Admin\Controllers\Merchant;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Admin\Controllers\Traits\Common;
use App\Models\GameManage\Game as GameManageGame;
use App\Models\GameManage\GameVendor;
use App\Models\Merchant\Game;
use App\Models\GameManage\GameType;
use App\Models\GameManage\GameTag;
use Illuminate\Support\Facades\DB;

/**
 * 遊戲清單
 */
class GameListController extends AdminController
{
    use Common;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '遊戲管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Game);
        $grid->column('pic', __('圖片'))->image(null, 100);
        $grid->column('info.vendor', __('遊戲商'))->display(function($v){
            return GameVendor::pluckKV()[$this->info['vendor_code']];
        });
        $grid->column('info.launch_code', __('啟動代碼'))->sortable();
        $grid->column('game_code', __('遊戲代碼'))->sortable();
        $grid->column('info.game_type', __('遊戲類型'))->display(function($v){
            return GameType::pluckKV()[$this->info['game_type']];
        });
        $grid->column('name', __('名稱'))->display(function($v){
            return $v[session('locale')]??$v['en'];
        });
        $grid->column('game_tag', __('遊戲標籤'));
        $grid->column('c_flag', __('自訂'))->label([
            '0' => 'default',
            '1' => 'warning'
        ]);
        $grid->column('status', __('狀態'))->using($this->formGameState());
        $grid->column('sort_order', __('排序'))->editable();
        $grid->model()->where('merchant_code', session('merchant_code'))->orderBy('game_code');

        $grid->disableExport(false);
        $grid->disableCreateButton();
        //
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->equal('info.vendor_code', __('遊戲商'))->select(GameVendor::pluckKV());
                $filter->equal('info.game_type', __('遊戲類型'))->select(GameType::pluckKV());
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('game_code', __('遊戲代碼'))
                ->select(Game::filterGameName(session('merchant_code'), session('locale'))->pluck('game_name', 'game_code'));
                $filter->equal('status', __('狀態'))->select($this->formGameState());
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
        $show = new Show(Game::findOrFail($id));

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

        $form = new Form(new Game);

        $form->hidden('id');
        $form->display('game_code', __('遊戲代碼'))->options(GameManageGame::pluckKV());
        $form->selectize('game_tag', __('遊戲標籤'));
        $form->keyValue('name', __('遊戲名稱'))->rules('required')->value(static::supportLang());
        $form->textarea('memo', __('備註'))->attribute('maxlength', 255);
        $form->select('status', __('狀態'))->options(static::formMerchantGameState());
        $form->switch('c_flag', __('自訂'))->states(static::formSwitchLocale('open'));
        $form->number('sort_order', __('排序'));
        $form->image('pic', __('圖片'))->rules('dimensions:ratio=167/110')->uniqueName()->help('size=167x110')->removable();
        $form->saving(function (Form $form) {

        });

        return $form;
    }
}
