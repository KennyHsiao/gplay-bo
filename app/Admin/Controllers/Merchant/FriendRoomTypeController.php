<?php

namespace App\Admin\Controllers\Merchant;

use App\Admin\Controllers\AdminController;
use App\Admin\Controllers\Traits\Common;
use App\Models\Merchant\FriendRoomType;
use App\Models\Platform\Company;
use App\Models\System\Currency;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Widgets\Table;

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
        $grid->column('merchant.name', __('商户'));
        $grid->column('', "子房型")
            ->expand(function ($model) {
                $headers = ["房型ID", "標籤顏色", "幣別", "小盲", "大盲", "前注", "買入", "人數", "狀態"];
                $data = [];
                foreach ($model->subfriendroomtypes as $subRoomType) {
                    $data[] = [
                        $subRoomType->arena_id,
                        "<span style='background-color: {$subRoomType->color}; width: 20px; height: 20px; display: inline-block;'></span>",
                        Currency::pluck('name', 'code')[$subRoomType->currency],
                        $subRoomType->small_blind,
                        $subRoomType->big_blind,
                        $subRoomType->ante,
                        $subRoomType->buy_in_amount,
                        $subRoomType->max_players,
                        $subRoomType->status == 1 ? '開放' : '關閉',
                    ];
                }
                return new Table($headers, $data);
            }, true);

        $grid->model()
            ->with('subfriendroomtypes')
            ->where('merchant_code', session('merchant_code'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('merchant_code', '商户')
                ->select(Company::where("type", "merchant")
                    ->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')
                    ->orderBy('parent_id')
                    ->pluck('name', 'code')
                );
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
        $form->select('merchant_code', __('商户'))
            ->options(
                Company::where("type", "merchant")
                    ->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')
                    ->orderBy('parent_id')
                    ->pluck('name', 'code')
            )->rules('required')
            ->default(session('merchant_code'))
            ->readonly();

        // $form->keyValue('title', __('房型名稱'))
        //     ->rules('required')
        //     ->value(static::supportLang());

        $form->hasMany("subfriendroomtypes", "子房型", function (Form\NestedForm $form) {
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
        });
        return $form;
    }
}
