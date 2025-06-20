<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController;
use App\Models\Player\Transaction;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Models\System\Language;
use Xn\Dx\DxDataGrid;
use Xn\Dx\DxDataGrid\Summary;

class DevExtremeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DevExtreme測試';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        // $grid = new Grid(new Language);
        $grid = new DxDataGrid(new Transaction);
        $grid->columnChooser(true)->dxBeforeSend(['m_code' => session('merchant_code')])
            ->addHeaderFilter()->addGroupPanel(false)
            ->selectionMode('single')->events([
                'onRowClick' => function(){
                    $script = <<<SCRIPT
                        function(e) {
                            console.log(e);
                        }
                    SCRIPT;
                    return $script;
                },
                'onCellClick' => function(){
                    $script = <<<SCRIPT
                        function(e) {
                            console.log(e);
                        }
                    SCRIPT;
                    return $script;
                }
            ])->summary((new Summary())->calculateCustomSummary(function(){
                $script = <<<SCRIPT
                    function(e) {
                        return e.trans_type;
                    }
                SCRIPT;
                return $script;
            }));
        $grid->column('id', __('序'))->sortOrder(false)->width(100);
        $grid->column('trans_type', __('名稱'))->calculateCellValue(function(){
            $script = <<<SCRIPT
                function(e) {
                    return e.trans_type;
                }
            SCRIPT;
            return $script;
        })->width(200);;
        $grid->column('trace_id', __('單號'))->total('count', ['showInColumn' => 'id'])->group('count', ['showInColumn' => 'trace_id']);
        $grid->column('balance', __('餘額'))->total('sum', ['valueFormat' => 'currency']);
        // filter
        // $grid->filter(function($filter){
        //     $filter->disableIdFilter();
        //     $filter->column(1/2, function ($filter) {
        //         $filter->like('code', __('代碼'));
        //     });
        // });
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
        $show = new Show(Language::findOrFail($id));

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
        $form = new Form(new Language);
        $form->hidden('id');
        $form->text('code', __('代碼'))->rules('required|unique:sys_languages,code,'.request()->input('id',0).',id');
        $form->text('name', __('名稱'))->rules('required');
        $form->saving(function (Form $form) {
            if ($form->code) {
                $form->code = strtolower($form->code);
            }
        });

        return $form;
    }
}
