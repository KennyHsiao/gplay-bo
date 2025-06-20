<?php

namespace App\Admin\Controllers\Line;

use App\Admin\Controllers\AdminController;
use Illuminate\Support\Str;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Facades\Admin;
use App\Models\Line\LineBotPush;
use App\Admin\Controllers\Traits\Common;
use App\Admin\Extensions\Tools\LineBotPushToolbar;
use App\Admin\Controllers\Traits\LineBotPushExt;
use App\Admin\Controllers\Traits\LineBotPushSendExt;

class LINEBotPushController extends AdminController
{
    use Common, LineBotPushExt, LineBotPushSendExt;

    static $push_target = ['all'=>'全部', 'user'=>'使用者', 'group'=>'群組'];

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'LINE訊息推播';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new LineBotPush);

        $grid->column('type', __('類型'))->display(function ($v){
            switch($v) {
                case 'text': return __('文字'); break;
                case 'image': return __('圖片'); break;
                case 'imagemap': return __('影像地圖'); break;
                case 'flex': return __('卡片訊息'); break;
            }
        });
        $grid->column('image', __('內容'))->display(function($val){

            switch ($this->type) {
                case 'imagemap':
                    $val = env('APP_URL')."/{$val}/1040";
                    return "<img src=\"{$val}\" class=\"img-thumbnail\" style=\"width:60px;\" onerror=\" this.src='/images/empty-avatar.png' \">";
                break;
                case 'image':
                    $val = env('APP_URL')."/uploads/{$val}";
                    return "<img src=\"{$val}\" class=\"img-thumbnail\" style=\"width:60px;\" onerror=\" this.src='/images/empty-avatar.png' \">";
                break;
                case 'text':
                    $val = Str::limit($this->message, 20);
                    return "<span>{$val}</span>";
                break;
                case 'flex':
                    if (!empty($val)) {
                        $val = env('APP_URL')."/uploads/{$val}";
                        return "<img src=\"{$val}\" class=\"img-thumbnail\" style=\"width:60px;\" onerror=\" this.src='/images/empty-avatar.png' \">";
                    } else {
                        $val = Str::limit($this->title, 20);
                        return "<span>{$val}</span>";
                    }
                break;
            }

        });
        $grid->column('targets', __('對象'))->display(function ($groups) {
            $groups = array_map(function ($group) {
                return "<span class='label label-success'>{$group['title']}</span>";
            }, $groups);
            return join('&nbsp;', $groups);
        });
        // $grid->column('target', __('對象'))->display(function($val){
        //     return LINEBotPushController::$push_target[$val];
        // });
        $grid->column('send_at', __('預定發送'));
        $grid->column('sent_at', __('發送於'));
        $grid->column('count', __('發送數量'));
        $grid->model()->where(function($q){
            $q->where('merchant_code', Admin::user()->merchant_code);
        })->orderBy('created_at', 'desc');
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('type', '類型')->select(['text' => '文字', 'image' => '圖片', 'imagemap' => '影像地圖', 'flex' => '卡片訊息']);
        });
        $script = <<<EOT
        $('a.push').on('click', function() {
            var self = this;
            swal.fire({
                title: "手動進行推播？",
                allowOutsideClick: false,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: '取消',
                confirmButtonText: '送出',
            }).then(function(result){
                if(result.value) {
                    swalDialog("資料處理中");
                    $.ajax({
                        method: 'post',
                        url: $(self).data('href'),
                        data: {
                            _token:LA.token
                        },
                        success: function (resp) {
                            console.log(resp);
                            //var result = JSON.parse(resp);
                            swal.close();
                            $.pjax.reload('#pjax-container');
                            toastr.success(resp['message']);
                        }
                    });
                }
            });

        });

EOT;
        Admin::script($script);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            //
            $actions->disableView();
            $actions->disableEdit();
            //
            if (empty($actions->row["sent_at"])){
                $_url = route("push.edit", ['type'=>$actions->row["type"], 'id'=>$actions->row["id"]]);
                $actions->appendHtml("<a href=\"$_url \" ><i class=\"fa fa-edit\"></i></a>");

                $_url = route("push.send", $actions->row["id"]);
                $actions->appendHtml("<a class=\"push\" href=\"javascript:;\" data-href=\"$_url \" style=\"margin-right:5px;\"><i class=\"fa fa-pushed\"></i></a>");
            }
        });

        $grid->tools(function ($tools) {
            $tools->append(new LineBotPushToolbar([
                [
                    'type' => 'text',
                    'label' => __('文字'),
                    'url' => route('push.create',['type'=>'text'])
                ] , [
                    'type' => 'image',
                    'label' => __('圖片'),
                    'url' => route('push.create',['type'=>'image'])
                ] ,[
                    'type' => 'imagemap',
                    'label' => __('影像地圖'),
                    'url' => route('push.create',['type'=>'imagemap'])
                ] ,[
                    'type' => 'flex',
                    'label' => __('卡片訊息'),
                    'url' => route('push.create',['type'=>'flex'])
                ] ,
            ]));
        });

        $grid->disableCreateButton();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(LineBotPush::class, function (Form $form) {
            $form->textarea('message', __('訊息'))->rules('required');
            $form->datetime('send_at', __('預定發送'))->rules('required');
            $form->saving(function ($form) {
                \Log::info( $form->model()->id);
            });
            // $form->setAction(route('push.create', $type));
        });
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $map = LineBotPush::find($id);
        if ($this->form()->destroy($id)) {
            $oldImage = $map->image;
            static::DeleteDirectory($oldImage);
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }

    /**
     * 移除資料夾
     *
     * @param [type] $dir
     * @return void
     */
    private static function DeleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!static::DeleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }
}
