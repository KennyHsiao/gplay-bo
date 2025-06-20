<?php

namespace App\Admin\Actions\Member;

use Xn\Admin\Actions\RowAction;
use Xn\Admin\Facades\Admin;

class AuthCodeReset extends RowAction
{
    protected $merchantCode;
    protected $account;

    public function __construct($merchantCode, $account)
    {
        parent::__construct();
        $this->merchantCode = $merchantCode;
        $this->account = $account;
    }

    protected function script()
    {
        $authCodeReset = route('api.auth-code-reset');
        $trans = [
            'title'               => trans('支付密碼重置'),
            'delete_confirm'      => trans('admin.delete_confirm'),
            'save_succeeded'      => trans('admin.save_succeeded'),
            'refresh_succeeded'   => trans('admin.refresh_succeeded'),
            'delete_succeeded'    => trans('admin.delete_succeeded'),
            'confirm'             => trans('admin.confirm'),
            'cancel'              => trans('admin.cancel'),
            'value_err'           => trans('密碼格式4碼以上英數'),
            // 'password_reset_url'  => route('members.chargePasswordReset')
        ];
        return <<<SCRIPT

$('.grid-auth_code_reset').on('click', function() {
    const account = $(this).data('id');
    const merchantCode = $(this).data('merchant');

    Swal.fire({
        title: "{$trans['title']}",
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: "{$trans['confirm']}",
        showLoaderOnConfirm: true,
        cancelButtonText: "{$trans['cancel']}",
        inputValidator: (value) => {
            if (value.length < 4) {
                return "{$trans['value_err']}";
            }
        },
    }).then((result) => {
        if (result.value.length >= 4) {
            $.post("{$authCodeReset}", {
                'merchant_code': merchantCode,
                'account': account,
                'password': result.value
            }, function(res) {
                if (res.code == 0) {
                    Swal.fire(res.message).then((res) => {
                        $.pjax.reload('#pjax-container');
                    });
                } else {
                    Swal.fire(res.message);
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

        return "<a class='btn btn-sm btn-default grid-auth_code_reset' data-merchant='{$this->merchantCode}' data-id='{$this->account}' style='margin-left:5px;'>". trans('支付密碼重置') ."</a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}
