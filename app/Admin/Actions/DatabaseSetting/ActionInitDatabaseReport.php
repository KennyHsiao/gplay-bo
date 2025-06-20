<?php

namespace App\Admin\Actions\DatabaseSetting;

use Xn\Admin\Actions\RowAction;
use Xn\Admin\Facades\Admin;

class ActionInitDatabaseReport extends RowAction
{
    public $name = 'init database';

    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {

        $trans = [
            'title'               => trans('初始化'),
            'delete_confirm'      => trans('admin.delete_confirm'),
            'save_succeeded'      => trans('admin.save_succeeded'),
            'refresh_succeeded'   => trans('admin.refresh_succeeded'),
            'delete_succeeded'    => trans('admin.delete_succeeded'),
            'confirm'             => trans('admin.confirm'),
            'cancel'              => trans('admin.cancel'),
            'url'                 => route('database.report.init')
        ];
        return <<<SCRIPT

$('.grid-init-database-report').on('click', function () {
    swal.fire({
        title: "{$trans['title']}",
        icon: 'warning',
        confirmButtonText: "{$trans['confirm']}",
        cancelButtonText: "{$trans['cancel']}",
        showLoaderOnConfirm: true,
        showCancelButton: true,
        allowOutsideClick: false,
    }).then((result) => {
        if (result.value) {
            swal.fire({
                title: "{$trans['title']}",
                allowEscapeKey: false,
                allowOutsideClick: false,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                    $.post("{$trans['url']}", {'code': $(this).data('id') }, function(res){
                        swal.fire(res.data.text).then((res) => {
                            $.pjax.reload('#pjax-container');
                        });
                    });
                }
            });
        }
    });
});

SCRIPT;
    }

    public function render()
    {
        Admin::script($this->script());

        return "<a class='btn btn-sm btn-warning grid-init-database-report' data-id='{$this->id}' style='margin-left:5px;'>".trans('報表資料庫')."</a>";
    }

    public function __toString()
    {
        return $this->render();
    }

}
