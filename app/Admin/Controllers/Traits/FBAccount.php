<?php

namespace App\Admin\Controllers\Traits;

use Xn\Admin\Facades\Admin;
use Xn\Admin\Form;
use Xn\Admin\Layout\Content;
use Illuminate\Http\Request;

trait FBAccount
{

    public function getFbBotAccount(Request $request, $id = null) {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('FB 帳號設置');
            $form = $this->fbBotForm($id);
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


    public function putFbBotAccount(Request $request, $id = null) {
        $id = $id ?? Admin::user()->id;
        return $this->fbBotForm($id)->update($id);
    }

    protected function fbBotForm($id) {
        $userModel = config('admin.database.users_model');
        return $userModel::form(function (Form $form) use ($id) {
            ### disable button
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
                $tools->disableView();
            });
            ###
            $form->display('username', trans('admin.username'));
            $form->display('name', trans('admin.name'));
            //$form->selectize('fb_wake_up', '關鍵字喚醒')->config('maxItems', 3)->help('最多3組');
            $form->textarea('default_response', '預設回應')->default('您可以從下方選單選擇我們的服務呦😊')->rules('max:255')
                    ->attribute('maxlength', 255)->attribute('name', 'default_response');
            // $form->textarea('wake_up', '關鍵字喚醒')->rules(function(){
            //     return [
            //         'regex:/^(\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]{2,4}\s*?,?\s*?)+$/',
            //         'max:60'
            //     ];
            // })->help("請以“,”逗點分隔Email");
            if (Admin::user()->isRole('administrator')) {
                $form->divider();
                $form->text('api_token', 'API TOKEN')->rules('required|max:60')->attribute('maxlength', 60);
                $form->text('fb_app_secret', 'App Secret')->rules('max:50')->help("應用程式密鑰")->attribute('maxlength', 50);
                $form->text('fb_validation_token', 'Validation Token')->rules('max:255')->help("驗證權杖")->attribute('maxlength', 255);
                $form->textarea('fb_page_access_token', 'Page Access Token')->rules('max:255')->help("粉絲頁存取權杖")
                        ->attribute(['maxlength' => 255]);
                $form->textarea('note', '備註')->rules('max:512')->attribute('maxlength', 512);
            }
            $form->setAction(route('supervisor.fb-bot', $id));
            $form->saved(function () use ($id) {
                admin_toastr(trans('admin.update_succeeded'));
                return redirect(route('supervisor.fb-bot', $id));
            });
        });
    }
}
