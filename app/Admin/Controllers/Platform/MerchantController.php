<?php

namespace App\Admin\Controllers\Platform;

use App\Admin\Controllers\AdminController;
use App\Admin\Controllers\Traits\Common;
use App\Models\GameManage\GameVendor;
use App\Models\Platform\Company;
use App\Models\Platform\Merchant;
use App\Models\System\Currency;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Admin;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use Xn\Admin\Widgets\Tab;
use Xn\Admin\Widgets\Table;

class MerchantController extends AdminController
{
    use Common;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商戶管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        Admin::script(
            <<<EOF
function updateStatusComboboxColor() {
    $('span[title=\"正常\"], span[title=\"Online\"]').parent().css('background-color', 'lightgreen');
    $('span[title=\"維護\"], span[title=\"Maintenance\"]').parent().css('background-color', 'orange');
    $('span[title=\"下架\"], span[title=\"Decommission\"]').parent().css('background-color', 'lightcoral');
    $('span[title=\"敬请期待\"], span[title=\"StayTuned\"]').parent().css('background-color', 'lightblue');
}

$(function() {
    updateStatusComboboxColor()
    $('body').on('change', '.grid-select-status, .form-control.status', function(){
        updateStatusComboboxColor()
    });
});
EOF


        );

        $company = Company::select(DB::raw("CONCAT('(',code,')',name) AS name"),'code')->where('type', 'merchant')->pluck('name', 'code')->toArray();
        $grid = new Grid(new Merchant());
        $grid->model()->orderBy('created_at', 'desc');
        $grid->column('agent_code', __('代理'));
        $grid->column('code', __('商戶'))->display(function($data)use($company){
            return $company[$data] ?? $data;
        })->expand(function ($v){
            return new Table(['Key', 'Value'], [
                __('AccessToken') => $v->access_token,
                __('SecretKey') => $v->secret_key,
            ]);
        });
        $grid->column('switch_transfer', __('轉點'))->switch(static::$switch_open);
        $grid->column('switch_departure', __('跨商戶轉點'))->switch(static::$switch_open);
        $grid->column('status', __('狀態'))->select2(static::formMerchantState(), true);
        $grid->column('fee_open', __('費率開關'))->switch(static::$switch_public);
        $grid->column('fee_rate', __('費率%'));
        $grid->column('fix_fee', __('固定費用'));
        $grid->column('min_transfer', __('最低交易額'));
        $grid->column('withdraw_threshold', __('出款審核'));
        $grid->column('memo', __('備註'));


        $grid->actions(function(Grid\Displayers\BtnActions $actions){
            $actions->disableView(true);
            // $actions->setBtnSize('sm');
        });
        $grid->setActionClass(Grid\Displayers\BtnActions::class);

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->column(1/3, function ($filter) {
                $filter->equal('agent_code', __('代理'))->select(Company::
                select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')->where('type', 'agent')->pluck('name', 'code'));
                $filter->equal('access_token', __('AccessToken'));
                // $filter->equal('currency', __('幣別'))->select(Currency::pluck('name', 'code'));
            });
            $filter->column(1/3, function ($filter) {
                $filter->selectGroup('code', __('商戶'))->options(Company::with(['merchants'=>function($q){
                    return $q->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'id', 'parent_id', 'code')->orderBy('order');
                }])->where('type', 'agent')->orderBy('order')->get()->toArray(), 'merchants', 'code');
                // $filter->equal('code', __('form.code'))->select(Company::selectOptions(function($q){
                //     return $q->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'id', 'parent_id', 'code');
                // }, null, 'code'));//->select(Company::where('type','merchant')->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')->pluck('name', 'code'));

            });
            $filter->column(1/3, function ($filter) {
                $filter->equal('status', __('狀態'))->select([
                    '0' => __('CLOSE'),
                    '1' => __('OPEN')
                ]);
            });

        });

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
        $show = new Show(Merchant::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('code', __('Code'));
        $show->field('access_token', __('Access token'));
        $show->field('secret_key', __('Secret key'));
        $show->field('wallet_api', __('Wallet api'));
        $show->field('wallet_token', __('Wallet token'));
        $show->field('website', __('Website'));
        $show->field('api_whitelist', __('Api whitelist'));
        $show->field('agent_code', __('Agent code'));
        $show->field('lang', __('Lang'));
        $show->field('balance', __('Balance'));
        $show->field('status', __('Status'));
        $show->field('memo', __('Memo'));
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
        $vendorSettingAPI = route("api.get-vendor-setting");
        $js = <<<EOF
        (function(){
            $('body').off('change', '[class*="vendor_code"]');
            $('body').on('change', '[class*="vendor_code"]', function(){
                var editorName = 'editor_'+$(this).attr('name').replaceAll(/\[|\]/ig,'_').replace('vendor_code','params');
                $.get('$vendorSettingAPI?code='+$(this).val(), function(data){
                    window[editorName].aceEditor.setValue(JSON.stringify(data.setting));
                });
            });
        })()
        EOF;
        Admin::script($js);

        $form = new Form(new Merchant());

        $mCode = Merchant::find(request()->route('merchant'))->code ?? "";
        $form->hidden('agent_code');
        $form->tab(__('基本資料'), function($form) use ($mCode){
            if ($form->isCreating()) {
                $form->select('code', __('代碼'))->options(
                    Company::whereRaw("type = 'merchant' AND code NOT IN (SELECT code FROM sys_merchants) OR code = '{$mCode}'")->select(DB::raw("CONCAT(name,'(',code,')') AS name"), 'code')->orderBy('parent_id')->pluck('name', 'code')
                )->rules('required');
            } else {
                $form->hidden('code');
                $form->display('code', __('代碼'));
            }
            $form->switch('switch_transfer', __('轉點'))->states(static::$switch_open)->default('1');
            $form->switch('switch_departure', __('跨商戶轉點'))->states(static::$switch_open)->default('0');
            $form->text('withdraw_threshold', __('出款審核'))->inputmask([
                'alias' => 'numeric',
                'allowMinus' => true,
                'digits' => 0,
                'max' => 9999999
            ])->default('0')->rules('required');
            $form->select('status', __('狀態'))->options(static::formMerchantState())->default('1');
            $form->textarea('memo', __('備註'))->attribute('maxlength', 256);
        })->tab(__('App配置'), function($form){
            // $form->select('wallet_type', __('錢包類型'))->options([
            //     'transfer' => __('轉帳'),
            //     'seamless' => __('單一'),
            // ])->rules('required')->default('transfer');
            // $form->keyValue('wallet_api', __('錢包接口'))->value([
            //     'access_token' => '',
            //     'secret_key' => '',
            //     'wallet_api' => '',
            //     'wallet_token' => '',
            // ])->rules('required');
            // $form->keyValue('wallet_method', __('錢包方法'))->value([
            //     'get_balance' => 'get_balance',
            // ])->rules('required');
            $form->text('access_token', __('AccessToken'));
            $form->text('secret_key', __('SecretKey'));
            $form->textarea('cashier', __('收銀台'));
        })->tab(__('遊戲商配置'), function($form){
            $form->hasMany('vendors', null, function (Form\NestedForm $form) {
                $form->select('vendor_code', __('遊戲商'))->options(GameVendor::pluckKV())
                    ->rules("required|unique:mc_vendors,vendor_code,{{sid}},id,merchant_code,".request('code'));
                $form->json('params', __('請求參數'))->rules('required');
                $form->select('status', __('狀態'))->options(static::formMerchantState())->default('1');
            });
        })->tab(__('LINE 配置'), function($form){
            // $form->divider(__('LINE 通知'));
            // $form->text('notify_client_id', __('Notify Client ID'))->rules('max:50')->attribute('maxlength', 50)->default($this->getGlobalParamValue('line-notify-client-id'));
            // $form->textarea('notify_client_secret', __('Notify Client Secret'))->rules('max:255')->attribute('maxlength', 255)->default($this->getGlobalParamValue('line-notify-client-secret'));
            $form->divider(__('LINE 機器人'));
            $form->text('api_token', __('Callback API Token'))->rules('max:60')->attribute('maxlength', 60);
            $form->text('line_bot_id', __('LINE Bot ID'))->rules(function(){
                return [
                    'max:20',
                    //'regex:/^@[A-Za-z0-9]+$/',
                ];
            })->help("<a data-fancybox href='/static/how_to_get_line_id.png' target='_blank'>".__('提示')."</a>");
            $form->text('line_m_channel_id', __('Message Channel ID'))->rules('max:50')->attribute('maxlength', 50);
            $form->text('line_m_channel_secret',__('Message Channel Secret'))->rules('max:50')->attribute('maxlength', 50);
            $form->textarea('line_m_channel_access_token', __('Message Channel Access Token'))->rules('max:255')->attribute('name', 'line_channel_access_token');
            $form->divider(__('LINE 登入'));
            $form->text('line_login_channel_id', __('Line Login Channel ID'))->rules('max:50')->attribute('maxlength', 50);
            $form->text('line_login_channel_secret', __('Line Login Channel Secret'))->rules('max:50')->attribute('maxlength', 50);
            $form->display('line_login_channel_access_token', __('Line Login Channel Access Token'));
        })->tab(__('費率配置'), function($form){
            $form->switch('fee_open', __('費率開關'))->states(static::$switch_open)->default('0');
            $form->text('min_transfer', __('最低交易額'))->inputmask([
                'alias' => 'numeric',
                'allowMinus' => true,
                'digits' => 2,
                'min' => 0.1,
                'max' => 9999999
            ])->rules('required');
            $form->text('fee_rate', __('費率%'))->inputmask([
                'alias' => 'numeric',
                'allowMinus' => true,
                'digits' => 3,
                'max' => 100
            ])->default('0.00')->rules('required');
            $form->text('fix_fee', __('固定費用'))->inputmask([
                'alias' => 'numeric',
                'allowMinus' => true,
                'digits' => 2,
                'max' => 9999999
            ])->default('0')->rules('required');
            $form->textarea('fee_memo', __('費率說明'))->attribute('maxlength', 100);
        })->tab(__('區塊鏈錢包'), function($form){
            $form->textarea('ton_words', __('Ton助記詞'));
            $form->textarea('tron_words', __('Tron助記詞'));
        });


        $form->saving(function (Form $form) {
            if ($form->isCreating()) {
                if (empty($form->access_token)) {
                    $form->access_token = md5($form->code);
                }
                if (empty($form->secret_key)) {
                    $form->secret_key = md5($form->access_token);
                }
                if (empty($form->wallet_token)) {
                    $form->wallet_token = md5(uniqid());
                }
            }
            if ($form->isCreating()) {
                if ($form->code) {
                    $merchant = Company::where('code', $form->code)->first();
                    $agent = Company::find($merchant->parent_id);
                    $form->agent_code = $agent->code;
                }
            }

            if ($form->ton_words && $form->model()->ton_words != $form->ton_words) {
                $form->ton_words = $this->aesEncrypt($form->ton_words, md5('giocoplay.com'), substr(md5('iph2018gf'), 0, 16));
            }

            if ($form->tron_words && $form->model()->tron_words != $form->tron_words) {
                $form->tron_words = $this->aesEncrypt($form->tron_words, md5('giocoplay.com'), substr(md5('iph2018gf'), 0, 16));
            }
        });

        // $form->confirmAuth(trans('admin.password_confirmation'), route('api.auth-confirm'));

        return $form;
    }

    private function aesEncrypt($plaintext, $key, $iv) {
        $cipher = "aes-256-cbc";
        $ciphertext = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($ciphertext);
    }
}
