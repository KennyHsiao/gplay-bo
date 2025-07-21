<?php

namespace App\Admin\Controllers\Merchant;

use App\Admin\Controllers\AdminController;
use App\Admin\Controllers\Traits\Common;
use App\Models\Merchant\RoomType;
use App\Models\Platform\Company;
use App\Models\System\Currency;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Form;
use Xn\Admin\Grid;

class RoomTypeController extends AdminController
{
    use common;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '房型';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RoomType());
        $grid->column('merchant.name', __('商户'));
        $grid->column('title', "房型名稱");
        $grid->column('arena_id', '房型ID')
            ->editable();
        $grid->column("color", "標籤顏色")->display(function ($v) {
            return "<span style='background-color: {$v}; width: 20px; height: 20px; display: inline-block;'></span>";
        });
        $grid->column('sort_order', "排序")
            ->editable()
            ->help('權重排序,數字越大越靠前');
        $grid->column('currency', "貨幣")->display(function ($v) {
            return Currency::pluck('name', 'code')[$v] ?? $v;
        });
        $grid->column('small_blind', "小盲");
        $grid->column('big_blind', "大盲");
        $grid->column('ante', "前注");
        $grid->column('buy_in_amount', "買入");
        $grid->column('max_players', "人數");
        $grid->column('status', '狀態')->switch($this->select_open);

        $grid->model()
            ->where('merchant_code', session('merchant_code'))
            ->orderBy('sort_order', 'desc')
            ->orderBy('id');

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('merchant_code', '商户')
                ->select(Company::where("type", "merchant")
                    ->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')
                    ->orderBy('parent_id')
                    ->pluck('name', 'code')
                );
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
        $form = new Form(new RoomType);
        $form->select('merchant_code', '商户')
            ->options(
                Company::where("type", "merchant")
                    ->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')
                    ->orderBy('parent_id')
                    ->pluck('name', 'code')
            )->rules('required')
            ->default(session('merchant_code'))
            ->readonly();
        $form->text('arena_id', '房型ID')
            ->rules('required');
        $form->keyValue('title', '房型名稱')
            ->rules('required')
            ->value(static::supportLang());
        $form->color('color', '標籤顏色')
            ->default('#000000')
            ->rules('required');
        $form->select('currency', '幣別')
            ->options(Currency::pluck('name', 'code'))
            ->rules('required');
        $form->decimal("small_blind", "小盲")
            ->options(['min' => 0, 'digits' => 2])
            ->rules('required');
        $form->decimal("big_blind", "大盲")
            ->options(['min' => 0, 'digits' => 2])
            ->rules('required');
        $form->decimal("ante", "前注")
            ->options(['min' => 0, 'digits' => 2])
            ->default(0)
            ->rules('required');
        $form->decimal("buy_in_amount", "買入")
            ->options(['min' => 0, 'digits' => 2])
            ->rules('required');
        $form->decimal("max_players", "人數")
            ->options(['min' => 0, 'digits' => 2])
            ->rules('required');
        $form->number('sort_order', '排序')
            ->help('權重排序')
            ->default(0);
        $form->switch('status', __('狀態'))
            ->options($this->select_open)
            ->default(1);
        return $form;
    }

}
