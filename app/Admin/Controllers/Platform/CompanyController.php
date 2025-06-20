<?php

namespace App\Admin\Controllers\Platform;

use App\Models\Platform\Company;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\MessageBag;
use Xn\Admin\Controllers\AdminController;
use Xn\Admin\Facades\Admin;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Layout\Column;
use Xn\Admin\Layout\Content;
use Xn\Admin\Layout\Row;
use Xn\Admin\Show;
use Xn\Admin\Tree;
use Xn\Admin\Widgets\Box;

class CompanyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '平台列表';


    public function index(Content $content)
    {
        $getIPUrl = route('api.get-companyip');
        $js = <<<EOF
        (function(){
            $('#btn_copyIP').on('click', function(){
                id = $('select[name="parent_id"]').val();
                $.getJSON( "$getIPUrl?q="+id, function( data ) {
                    if (data.ip) {
                        data.ip.split(',').forEach(function(ip){
                            myselectize[0].selectize.addOption({code:ip, name:ip});
                            myselectize[0].selectize.addItem(ip);
                        })
                    }
                });
            });
        })();
        EOF;
        Admin::script($js);

        $getCodeUrl = route('api.get-unique-company-code');
        $jsCode = <<<EOF
        (function(){
            $('select[name="type"]').on('change', function(){
                type = $(this).val();
                $.getJSON( "$getCodeUrl?q="+type, function( data ) {
                    $('input[name="code"]').val(data.code);
                });
            });
            $(document).ready(function(){
                $('select[name="type"]').trigger('change');
            });
        })();
        EOF;
        Admin::script($jsCode);
        return $content
            ->title(trans('平台列表'))
            ->description(trans('admin.list'))
            ->row(function (Row $row) {
                $row->column(6, $this->treeView()->render());

                $row->column(6, function (Column $column) {
                    $form = new \Xn\Admin\Widgets\Form();
                    $form->action(admin_url('platform/companies'));
                    $form->hidden('id');
                    $form->select('parent_id', trans('父公司'))
                        ->options(Company::selectOptions())
                        ->rules('required_if:type,"merchant"');
                    $form->select('type', __('類型'))->options([
                        'agent' => __('agent'),
                        'merchant' => __('merchant'),
                    ])->rules('required')->default('agent');
                    $form->hidden('code')->rules('required');
                    // $form->text('code', __('code'))->rules('required|min:3|alpha_num|unique:sys_companies,code,'.request()->input('id',0).',id')->attribute('maxlength', 4)
                    //     ->help(__('company.code_length'));
                    $form->text('name', __('名稱'))->attribute('maxlength', 64)->rules('required');

                    $form->selectize('ip_whitelist', __('白名單'));
                    // $form->currency('c_rate', __('currency_rate'))->default(1);
                    $form->html("<span class='btn btn-sm btn-success' id='btn_copyIP'>".__("複製父IP")."</span>");
                    $form->hidden('_token')->default(csrf_token());
                    $column->append((new Box(trans('admin.new'), $form))->style('success'));
                });
            });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Company());

        $grid->column('id', __('Id'));
        $grid->column('parent_id', __('Parent id'));
        $grid->column('code', __('Code'));
        $grid->column('name', __('Name'));
        $grid->column('type', __('Type'));
        $grid->column('ip_whitelist', __('Ip whitelist'));
        $grid->column('order', __('Order'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Company::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('parent_id', __('Parent id'));
        $show->field('code', __('Code'));
        $show->field('name', __('Name'));
        $show->field('type', __('Type'));
        $show->field('ip_whitelist', __('Ip whitelist'));
        $show->field('order', __('Order'));
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

        $getIPUrl = route('api.get-companyip');
        $js = <<<EOF
        (function(){
            $('#btn_copyIP').on('click', function(){
                id = $('select[name="parent_id"]').val();
                $.getJSON( "$getIPUrl?q="+id, function( data ) {
                    if (data.ip) {
                        data.ip.split(',').forEach(function(ip){
                            myselectize[0].selectize.addOption({code:ip, name:ip});
                            myselectize[0].selectize.addItem(ip);
                        })
                    }
                });
            });
        })();
        EOF;
        Admin::script($js);

        $form = new Form(new Company());

        $form->hidden('id');
        $form->hidden('code');
        $form->select('parent_id', trans('父公司'))->options(Company::selectOptions());
        $form->select('type', __('類型'))->options([
            'agent' => __('agent'),
            'merchant' => __('merchant'),
        ])->rules('required');
        $form->display('code', __('代碼'));
        $form->text('name', __('名稱'))->attribute('maxlength', 64)->rules('required');
        $form->selectize('ip_whitelist', __('白名單'));
        $form->html("<span class='btn btn-sm btn-success' id='btn_copyIP'>".__("複製父IP")."</span>");



        $form->saving(function (Form $form) {
            //
            if($form->type == 'agent' && $form->parent_id != '0') {
                $error = new MessageBag([
                    'title'   => __('code'),
                    'message' => __('only_support_1_layer_agent')."-".$form->code,
                ]);
                return back()->with(compact('error'));
            }
            if($form->type == 'merchant' && $form->parent_id == '0') {
                $error = new MessageBag([
                    'title'   => __('code'),
                    'message' => __('must_under_a_agent')."-{$form->code}",
                ]);
                return back()->with(compact('error'));
            }

            $form->code = strtoupper($form->code);

            Cache::forget("company_{$form->code}_ip");
        });
        return $form;
    }

    protected function treeView()
    {
        return Company::tree(function (Tree $tree) {
            $tree->disableCreate();
            $tree->branch(function ($branch) use ($tree){
                $type = "";
                switch($branch['type']) {
                    case 'agent': $type = "<span class='label label-success'>".__($branch['type'])."</span>" ;break;
                    case 'merchant': $type = "<span class='label label-default'>".__($branch['type'])."</span>" ;break;
                }

               //  $c= Company::where('code',$branch['code'])->first();
               // var_dump($c->merchant);


                $payload = "<i class='fa fa-bars'></i>&nbsp;<strong>({$branch['code']}){$branch['name']}</strong>&nbsp;&nbsp;<label style='margin-left:20px;'>{$type}</label>&nbsp;&nbsp;";

                return $payload;
            });
        })->nestable(['maxDepth'=>2]);
    }
}
