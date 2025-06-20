<?php

namespace App\Admin\Controllers\Merchant;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use App\Admin\Controllers\Traits\LIFFSync;
use App\Models\Platform\Company;
use App\Models\Merchant\Hyperlink;
use Illuminate\Support\Facades\DB;

class HyperlinkController extends AdminController
{
    use LIFFSync;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '超連結管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Hyperlink());
        $grid->column('merchant', __('商户'))->display(function($v){
            return "{$v['name']}({$v['code']})";
        });
        $grid->column('title', __('標題'))->editable();
        $grid->column('url', __('連結'));//->editable('textarea');
        $grid->column('liff_id', __('LIFF'))->display(function($v){
            return "<a href='https://liff.line.me/$v'>https://liff.line.me/$v</a> ";
        });
        $grid->column('scan_qr', __('掃QR'))->label();
        $grid->column('module_mode', __('模組模式'))->label();
        $grid->column('size', __('高度'))->label([
            'full' => 'default',
            'tall' => 'warning',
            'compact' => 'success',
        ]);
        $grid->column('scopes', __('權限'))->label('default');
        $grid->model()->orderBy('merchant_code');

        #### LIFF ### 最多 30組
        // $count = Hyperlink::where(function($q){
        //     $q->where('merchant_code', Admin::user()->merchant_code)->where('liff_id','>','');
        // })->count();

        // if ($count >= 30) {
        //     $grid->disableCreateButton();
        // }
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
        $switchOptions = [
            'on'  => ['value' => true, 'text' => __('開啟'), 'color' => 'primary'],
            'off' => ['value' => false, 'text' => __('關閉'), 'color' => 'danger'],
        ];

        $form = new Form(new Hyperlink);
        $form->hidden('id');
        $form->hidden('liff_id');
        $form->display('liff_id',__('LIFF ID'));
        $form->select('merchant_code', __('商户'))->options(
            Company::where("type","merchant")->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')->orderBy('parent_id')->pluck('name', 'code')
        )->rules('required');
        $form->text('title', __('標題'))->rules('required|max:12')->attribute('maxlength', 12)->help(__('請勿使用“line”關鍵字'));
        $form->select('slug', __('識別'))->options(['payment'=>__('支付-環境參數用')])->help(__('特殊目的用'));
        $form->textarea('url', __('連結'))->rules(function(){
            return [
                "regex:/^(https:\/\/|http:\/\/|route:\/\/|tel:\d*)/",
            ];
        })->attribute('maxlength', 1024)
        ->help('‘https://’、‘http://’、‘tel:0987654321’ 、’route://gpay.home \ route://game.pgsoft’《LINE客服範例：https://line.me/R/ti/p/@123456》 《LINE帳號分享：https://line.me/R/nv/recommendOA/@123456》
        《範例 https://ui-gpoints.vercel.app?m_code=QTR&liff_id=1657694289-8r1PONP0》');
        $form->switch('scan_qr', __('掃QR'))->states($switchOptions);
        $form->switch('module_mode', __('模組模式'))->states($switchOptions);
        $form->radio('size', __('高度'))->options(['full' => __('滿'), 'tall' => __('高'), 'compact' => __('袖珍')]);
        $form->checkbox('scopes', __('權限'))->options([
            'chat_message.write' => __('chat_message.write'),
            'openid' => __('openid'),
            'profile' => __('profile'),
            'email' => __('email')
        ]);
        $form->saving(function (Form $form) {
            // if ($form->model()->liff_id) {
            //     $this->deleteLIFF($form->model()->liff_id, $form->merchant_code);
            // }
            $isRoute = false;
            if(strpos($form->url, 'route://') !== false) {
                $view = explode("://", $form->url)[1];
                $form->url = route($view, ['id' => strtolower($form->merchant_code)]);
                $isRoute = true;
            }

            if ($form->slug === 'payment' || $isRoute) {
                if ($form->model()->liff_id) {
                    if(strpos($form->url, 'm_code') === false) {
                        $form->url = $form->url . "?m_code=".$form->merchant_code . "&liff_id=".$form->model()->liff_id;
                    }
                    $liff = $this->updateLIFFId($form->model()->liff_id,
                        $form->url, $form->merchant_code, $form->title, $form->scan_qr, $form->module_mode, $form->size, $form->scopes);
                } else {
                    $liff = $this->fetchLIFFId($form->url, $form->merchant_code, $form->title, $form->scan_qr, $form->module_mode, $form->size, $form->scopes);
                    if(isset($liff['liffId'])) {
                        $form->liff_id = $liff['liffId'];
                        if(strpos($form->url, 'm_code') === false) {
                            $form->url = $form->url . "?m_code=".$form->merchant_code . "&liff_id=".$form->liff_id;
                        }
                        $liff = $this->updateLIFFId($form->liff_id,
                            $form->url, $form->merchant_code, $form->title, $form->scan_qr, $form->module_mode, $form->size, $form->scopes);
                    }
                }
            } else {
                if ($form->model()->liff_id) {
                    $liff = $this->updateLIFFId($form->model()->liff_id,
                        $form->url, $form->merchant_code, $form->title, $form->scan_qr, $form->module_mode, $form->size, $form->scopes);
                } else {
                    $liff = $this->fetchLIFFId($form->url, $form->merchant_code, $form->title, $form->scan_qr, $form->module_mode, $form->size, $form->scopes);
                    if(isset($liff['liffId'])) {
                        $form->liff_id = $liff['liffId'];
                    }
                }
            }
        });

        return $form;
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
        $rec = DB::select('select liff_id, merchant_code from hyperlinks where id = ?', [$id])[0];
        $this->deleteLIFF($rec->liff_id, $rec->merchant_code);
        if ($this->form()->destroy($id)) {
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
}
