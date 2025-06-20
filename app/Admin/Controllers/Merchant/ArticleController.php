<?php

namespace App\Admin\Controllers\Merchant;

use App\Admin\Controllers\AdminController;
use App\Models\Merchant\Article;
use App\Models\Platform\Company;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Facades\Admin;
use Xn\Admin\Layout\Content;

class ArticleController extends AdminController
{

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '文章管理';

    // /**
    //  * Edit interface.
    //  *
    //  * @param mixed   $id
    //  * @param Content $content
    //  *
    //  * @return Content
    //  */
    // public function edit($id, Content $content)
    // {
    //     Admin::disablePjax();
    //     return parent::edit($id, $content);
    // }

    // /**
    //  * Create interface.
    //  *
    //  * @param Content $content
    //  *
    //  * @return Content
    //  */
    // public function create(Content $content)
    // {
    //     Admin::disablePjax();
    //     return parent::create($content);
    // }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Article());

        $grid->column('merchant.name', __('商户'));
        $grid->column('title', __('標題'));
        $grid->column('created_at', __(trans('admin.created_at')));
        $grid->column('updated_at', __(trans('admin.updated_at')));

        $grid->model()->orderBy('merchant_code');
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('merchant_code', __('商户'))->select(Company::where("type","merchant")->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')->orderBy('parent_id')->pluck('name', 'code'));
            $filter->like('title', __('標題'));
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
        $form = new Form(new Article);
        $form->select('merchant_code', __('商户'))->options(
            Company::where("type","merchant")->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')->orderBy('parent_id')->pluck('name', 'code')
        )->rules('required');
        $form->select('slug', __('識別'))->options(['policy'=>__('服務聲明')])->help(__('特殊目的用'));
        $form->text('title', __('標題'))->rules('required')->attribute('maxlength', 128);
        $form->editor('content', __('內容'));

        $form->hasMany('comments',null, function($form){
            $form->select('merchant_code', __('商户'))->options(
                Company::where("type","merchant")->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')->orderBy('parent_id')->pluck('name', 'code')
            )->rules('required');
            $form->select('slug', __('識別'))->options(['policy'=>__('服務聲明')])->help(__('特殊目的用'));
            $form->text('title', __('標題'))->rules('required')->attribute('maxlength', 128);
            // $form->editor('content', __('內容'));
        });
        return $form;
    }
}
