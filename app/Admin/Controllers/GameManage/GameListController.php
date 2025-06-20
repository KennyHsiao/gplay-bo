<?php

namespace App\Admin\Controllers\GameManage;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Admin\Controllers\Traits\Common;
use App\Models\GameManage\GameType;
use App\Models\GameManage\GameVendor;
use App\Models\GameManage\Game;
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
        $grid->column('gameVendor.name', __('遊戲商'))->display(function($v){
            return $v[session('locale')]??$v['en'];
        });
        $grid->column('launch_code', __('啟動代碼'))->sortable();
        $grid->column('game_code', __('遊戲代碼'))->sortable();
        $grid->column('gameType.name', __('遊戲類型'))->display(function($v){
            return $v[session('locale')]??$v['en'];
        });
        $grid->column('name', __('名稱'))->display(function($v){
            return $v[session('locale')]??$v['en'];
        });
        $grid->column('game_tag', __('遊戲標籤'));
        $grid->column('status', __('狀態'))->select2($this->formGameState());
        $grid->model()->orderBy('vendor_code')->orderBy('game_code');

        $grid->disableExport(false);
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->equal('vendor_code', __('遊戲商'))->select(GameVendor::pluckKV());
                $filter->equal('game_type', __('遊戲類型'))->select(GameType::filterGameType()->pluck('type_name', 'code'));
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('game_code', __('遊戲代碼'))
                ->select(Game::filterGameName(session('locale'))->pluck('game_name', 'game_code'));
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
        // dd(GameTag::filterGameTag()->pluck('tag_name'));
        $form = new Form(new Game);

        $form->hidden('id');
        $form->hidden('launch_code');
        $form->select('vendor_code', __('遊戲商'))->options(GameVendor::pluckKV())->rules('required');
        $form->select('game_type', __('遊戲類型'))->options(GameType::pluckKV())->rules('required');
        $form->text('game_code', __('遊戲代碼'))->rules('required|min:4|alpha_num|unique:gm_games,game_code,'.request()->input('id',0).',id')->attribute('maxlength', 128);
        $form->selectize('game_tag', __('遊戲標籤'))->options(GameTag::filterGameTag(session('locale'))->pluck('tag_name', 'code'));
        $form->keyValue('name', __('遊戲名稱'))->rules('required')->value(static::supportLang());
        $form->textarea('memo', __('備註'))->attribute('maxlength', 255);
        $form->select('status', __('狀態'))->options(static::formGameState());
        $form->image('pic', __('圖片'))->rules('dimensions:ratio=167/110')->uniqueName()->help('size=167x110')->removable();
        $form->saving(function (Form $form) {
            if (request('vendor_code')) {
                $form->launch_code = strtolower(request('vendor_code') . "_" . request('game_code'));
            }
        });

        return $form;
    }
}
