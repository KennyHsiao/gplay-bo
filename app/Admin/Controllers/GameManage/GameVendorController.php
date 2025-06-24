<?php

namespace App\Admin\Controllers\GameManage;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Admin\Controllers\Traits\Common;
use App\Models\GameManage\GameType;
use App\Models\GameManage\GameVendor;
use App\Models\System\Currency;
use App\Models\System\Language;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Widgets\Table;

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
        $grid->column('code', __('代碼'));
        $grid->column('_name', __('名稱'))->display(function($v){
            return json_decode(json_encode($this->name),true)[session('locale')]??"-";
        })->expand(function ($v){
            return new Table([__('Key'), __('Value')], $this->params??[]);
        });
        $grid->column('gameType.name', __('遊戲類型'))->display(function($v){
            return $v[session('locale')]??$v['en']??"-";
        });
        $grid->column('ip_whitelist', __('白名單'))->display(function($v){
            return explode(',', $v);
        })->label()->width(350);
        $grid->column('filter_ip', __('過濾IP'))->switch(static::formSwitchLocale('public'));
        $grid->column('wallet_type', __('錢包類型'))->switch(static::switchWalletType());
        $grid->column('status', __('狀態'))->switch(static::formSwitchLocale('public'));
        $grid->model()->orderBy('id');
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->equal('code', __('代碼'))->select(GameVendor::pluckKV());
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('status', __('狀態'))->select(static::switchPublic());
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
        $digitSetting = [
            'alias' => 'numeric',
            'allowMinus' => true,
            'digits' => 2,
            'max' => 999.99
        ];

        $form = new Form(new GameVendor);
        $form->hidden('id');
        $form->tab(__('基本資料'), function($form){
            if ($form->isCreating()) {
                $form->text('code', __('代碼'))->rules('required|min:2|alpha_num|unique:gm_vendors,code,'.(empty(request()->input('id'))?0:request()->input('id')).',id')->attribute('maxlength', 4);
            } else {
                $form->hidden('code');
                $form->display('code', __('代碼'));
            }
            $form->image('pic', __('圖片'))->rules('dimensions:ratio=267/130')->uniqueName()->help('size=267x130')->removable();
            $form->keyValue('name', __('名稱'))->rules('required')->value(static::supportLang());
            $form->select('game_type', __('遊戲類型'))->options(GameType::pluckKV())->rules('required');
            $form->switch('wallet_type', __('錢包類型'))->states(static::switchWalletType());
            $form->keyValue('params', __('請求參數'));
            $form->switch('filter_ip', __('過濾IP'))->states(static::formSwitchLocale('public'));
            $form->selectize('ip_whitelist', __('白名單'));
            $form->switch('status', __('狀態'))->states(static::formSwitchLocale('public'));
            $form->textarea('memo', __('備註'))->attribute('maxlength', 256);
        })->tab(__('幣別'), function($form)use($digitSetting){
            $form->hasMany('currencies', null, function ($table)use($digitSetting) {
                $table->select('code', __('幣別'))->options(Currency::pluck('name', 'code'))->rules('required');
                $table->text('rate', __('比率'))->inputmask($digitSetting)->default(1)->rules('required');
            })->useTable();
        })->tab(__('語系'), function($form){
            $form->keyValue('lang', __('語系'))->default(Language::select('code', DB::raw("code as name"))->pluck('name', 'code'));
        });
        $form->ignore(['id']);
        //保存前回调
        $form->saving(function (Form $form) {
            if($form->isCreating()) {
                $form->code = strtoupper($form->code);
            }
        });
        return $form;
    }
}
