<?php

namespace App\Admin\Actions\Member;

use Xn\Admin\Actions\RowAction;
use Xn\Admin\Facades\Admin;

class Withdraw extends RowAction
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
        $withdrawUrl = route('api.withdraw');
        $trans = [
            'title'             => trans('提領'),
            'confirm'           => trans('確認'),
            'cancel'            => trans('取消'),
            'value_err'         => trans('數值有誤'),
            // 'deposit_url'       => trans('members.buttons.deposit'),
            'deposit'           => trans('提領'),
            'deposit_value'     => trans('提領金額'),
            'remark'            => trans('附言'),
            'fill_remark'       => trans('附言'),
            'key'               => trans('密碼'),
            'fill_key'          => trans('密碼'),
            'step_finish'       => trans('step_finish'),
            'check_value'       => trans('check_value'),
            'success'           => trans('success'),
            'remark_value_error' => trans('members.remark_value_error'),
            'value_empty_error'  => trans('members.value_empty_error')
        ];

        return <<<SCRIPT
$('.grid-withdraw').on('click', function() {
    const account = $(this).data('id');
    const merchantCode = $(this).data('merchant');
    const steps = ['1', '2', '3']
    const Queue = Swal.mixin({
        input: 'text',
        confirmButtonText: "{$trans['confirm']}",
        cancelButtonText: "{$trans['cancel']}",
        showCancelButton: true,
        progressSteps: steps,
        inputAttributes: {
            maxlength: 28
        }
    })

    async function pipe() {
          const step1 = await Queue.fire({
            currentProgressStep: 0,
            title: "{$trans['deposit']}",
            text: "{$trans['deposit_value']}",
            inputValidator: (value) => {
                var numberPatt = new RegExp('^[0-9]{1,}[.]{0,1}[0-9]{0,2}$');
                if (!numberPatt.test(value) || parseFloat(value) <= 0) {
                    return "{$trans['value_err']}";
                }
            },
          })
          if (step1.isDismissed) return;


          const step2 = await Queue.fire({
                currentProgressStep: 1,
                title: "{$trans['remark']}",
                text: "{$trans['fill_remark']}",
                inputValidator: (value) => {
                    if (value.length == 0 || value == null) {
                        return "{$trans['remark_value_error']}";
                    }
                },
          })
           if (step2.isDismissed) return;


          const step3 = await Queue.fire({
                currentProgressStep: 2,
                title: "{$trans['key']}",
                input: "password",
                text: "{$trans['fill_key']}",
                inputValidator: (value) => {
                    if (value.length == 0) {
                        return "{$trans['value_empty_error']}";
                    }
                },
            })
           if (step3.isDismissed) return;



        $.post("{$withdrawUrl}", {
            'merchant_code': merchantCode,
            'account': account,
            'amount': step1.value,
            'memo': step2.value,
            'password': step3.value,
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
    pipe()
});
SCRIPT;
    }

    public function render()
    {
        Admin::script($this->script());

        return "<a class='btn btn-sm btn-warning grid-withdraw' data-merchant='{$this->merchantCode}' data-id='{$this->account}' style='margin-left:5px;'>".trans('提領')."</a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}
