<?php

namespace App\Admin\Controllers\Merchant;

use App\Admin\Controllers\AdminController;
use App\Models\Merchant\Marquee;
use App\Models\Platform\Company;
use App\Models\System\Language;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Illuminate\Support\Facades\DB;

class MarqueeController extends AdminController
{

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '跑馬燈管理';


    static function formPlayUnitLocale() {
        $data = [
            'd' => __('form.day'),
            'h' => __('form.hour'),
            'm' => __('form.minute'),
        ];
        return $data;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Marquee());

        $grid->column('merchant.name', __('商户'));
        $grid->column('content', __('內容'));
        $grid->column('publish_date', __('公告日期'))->display(function($v){
            return "{$this->start_time} ~ {$this->end_time}";
        });
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
        $form = new Form(new Marquee);
        $form->select('merchant_code', __('商户'))->options(
            Company::where("type","merchant")->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')->orderBy('parent_id')->pluck('name', 'code')
        )->rules('required');
        $form->textarea('content', __('內容'))->attribute('maxlength', 255)->rules('required');
        $form->datetimeRange('start_time', 'end_time', __('公共日期'))->rules('required');
        $form->select('lang', __('語系'))->options(Language::pluck('name', 'code'))->rules('required');
        $form->number('sort_order', __('排序'))->default(0);
        return $form;
    }
}
