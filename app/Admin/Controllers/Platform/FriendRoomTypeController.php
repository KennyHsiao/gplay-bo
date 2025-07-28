<?php

namespace App\Admin\Controllers\Platform;

use App\Admin\Controllers\AdminController;
use App\Admin\Controllers\Traits\Common;
use App\Models\Platform\FriendRoomType;
use App\Models\System\Currency;
use Xn\Admin\Form;
use Xn\Admin\Grid;

class FriendRoomTypeController extends AdminController
{
    use common;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '好友房房型';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FriendRoomType());

        $grid->column('arena_id', "房型ID")->editable();
        $grid->column("color", "標籤顏色")->display(function ($v) {
            return "<span style='background-color: {$v}; width: 20px; height: 20px; display: inline-block;'></span>";
        });
        $grid->column('sort_order', "排序")
            ->editable()
            ->help('權重排序,數字越大越靠前');
        $grid->column('small_blind', "小盲");
        $grid->column('big_blind', "大盲");
        $grid->column('ante', "前注");
        $grid->column('buy_in_amount', "買入");
        $grid->column('max_players', "人數");
        $grid->column('status', '狀態')->switch($this->select_open);

        $grid->model()->orderBy('sort_order', 'desc');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
        });

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();
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
        $form = new Form(new FriendRoomType());
        $form->select('currency', '幣別')
            ->options(Currency::pluck('name', 'code'))
            ->rules('required');
        $form->color('color', '標籤顏色')
            ->default('#000000')
            ->rules('required');
        $form->text('arena_id', '房型ID')
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
