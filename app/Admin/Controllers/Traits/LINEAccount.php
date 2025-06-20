<?php

namespace App\Admin\Controllers\Traits;

use Xn\Admin\Facades\Admin;
use Xn\Admin\Form;
use Xn\Admin\Layout\Content;
use Illuminate\Http\Request;

trait LINEAccount
{
    public function getLineBotAccount(Request $request, $id = null) {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('LINE 帳號設置');
            $form = $this->lineBotForm($id);
            $form->tools(
                function (Form\Tools $tools) {
                    $tools->disableList();
                }
            );
            //
            $id = $id ?? Admin::user()->id;
            $content->body($form->edit($id));
        });
    }


    public function putLineBotAccount(Request $request, $id = null) {
        $id = $id ?? Admin::user()->id;
        return $this->lineBotForm($id)->update($id);
    }

    protected function lineBotForm($id) {
        $userModel = config('admin.database.users_model');
        $form = new Form(new $userModel());

        $form->display('line_id', 'LINE ID');
        $form->display('username', trans('admin.username'));
        $form->display('name', trans('admin.name'));
        // $form->selectize('line_wake_up', '關鍵字喚醒')->config('maxItems', 3)->help('最多3組');
        $form->textarea('default_response', '預設回應')->default('您可以從下方選單選擇我們的服務呦😊')->rules('max:255')
                ->attribute('maxlength', 255)->attribute('name', 'default_response');
        // $form->textarea('wake_up', '關鍵字喚醒')->rules(function(){
        //     return [
        //         'regex:/^(\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]{2,4}\s*?,?\s*?)+$/',
        //         'max:60'
        //     ];
        // })->help("請以“,”逗點分隔Email");
        $form->setAction(route('supervisor.line-bot', $id));
        $form->saved(function () use ($id) {
            admin_toastr(trans('admin.update_succeeded'));
            return redirect(route('supervisor.line-bot', $id));
        });

        return $form;
    }
}
