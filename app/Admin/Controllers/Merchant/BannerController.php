<?php

namespace App\Admin\Controllers\Merchant;

use App\Admin\Controllers\AdminController;
use App\Models\Merchant\Banner;
use App\Models\Platform\Company;
use App\Models\System\Language;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Facades\Admin;
use Xn\Admin\Layout\Content;

class BannerController extends AdminController
{

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '橫幅圖管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner());

        $grid->column('merchant.name', __('商户'));
        $grid->column('pic', __('圖片'))->image(null, 100);
        $grid->column('link', __('連結'));
        $grid->column('link_type', __('連結類型'));
        $grid->column('lang', __('語系'));
        $grid->column('sort_order', __('排序'))->editable();

        $grid->model()->orderBy('sort_order', 'desc')->orderBy('id');
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('merchant_code', __('商户'))->select(Company::where("type","merchant")->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')->orderBy('parent_id')->pluck('name', 'code'));
            $filter->equal('lang', __('語系'))->select(Language::pluck('name', 'code'));
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
        $form = new Form(new Banner);
        $form->select('merchant_code', __('商户'))->options(
            Company::where("type","merchant")->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')->orderBy('parent_id')->pluck('name', 'code')
        )->rules('required');
        $form->image('pic', __('圖片'))->rules('dimensions:ratio=375/176')->uniqueName()->help('size=375x176')->removable();
        $form->select('link_type', __('連結類型'))->options([
            '' => __('空'),
            'game' => __('遊戲啟動'),
            'link' => __('外部連結'),
        ]);
        $form->textarea('link', __('連結'))->attribute('maxlength', 255);
        $form->select('lang', __('語系'))->options(Language::pluck('name', 'code'))->rules('required');
        $form->number('sort_order', __('排序'))->default(0);
        return $form;
    }
}
