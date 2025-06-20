<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Xn\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Xn\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use Xn\Admin\Facades\Admin;
use Xn\Admin\Form;
use Xn\Admin\Grid;

Xn\Admin\Form::forget(['map']);

Grid::init(function (Grid $grid) {
    $grid->tools(function ($tools) {
        $tools->batch(function ($batch) {
            $batch->disableDelete();
        });
    });
    // $grid->disableActions();

    // $grid->disablePagination();

    if (!Admin::user()->isRole('administrator')) {

        if (!session('permission')['create']) {
            $grid->disableCreateButton();
        }
    }
    // $grid->disableFilter();

    $grid->disableRowSelector();

    // $grid->disableColumnSelector();

    // $grid->disableTools();

    $grid->disableExport();

    $grid->actions(function (Grid\Displayers\Actions $actions) {
        $actions->disableView();
        //
        if (!Admin::user()->isRole('administrator')) {
            $actions->disableDelete();
            $actions->disableEdit();

            if (session('permission')['update']) {
                $actions->disableEdit(false);
            }
            if (session('permission')['delete']) {
                $actions->disableDelete(false);
            }
        }
    });
});

Form::init(function (Form $form) {

    $form->disableEditingCheck();

    $form->disableCreatingCheck();

    $form->disableViewCheck();

    $form->tools(function (Form\Tools $tools) {
        $tools->disableDelete();
        $tools->disableView();
        // $tools->disableList();
    });
});

$mCode = session('merchant_code');
$timezone = session('timezone');
$lang = session('locale');

Admin::script("
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
        }
    });
");

Admin::script("
    // 更新站內消息
    function updateInternalMessage(res){
        $('.messages-menu .message-count').text(res['message'].length);
        var internalMessage = $('.internal-message');
        internalMessage.empty();
        res['message'].forEach(function(msg) {
            if ('content' in document.createElement('template')) {
                var t = document.querySelector('#t-message').content.cloneNode(true);
                $(t).find('.modal-info').attr('data-title', msg['title']).attr('data-message', msg['content']);
                $(t).find('.message-title').text(msg['title']);
                $(t).find('.message-content').text(msg['content']);
                $(t).find('.message-timestamp').text(moment(parseInt(msg['timestamp'])).fromNow());
                internalMessage.append(
                    t
                );
            }
        });
    }
    function right(str, num) {
        return str.substring(str.length-num,str.length)
    }
    function format(state) {
        if (!state.id) return state.text; // optgroup
        return \"<img class='flag' src='/countryflags/\" + right(state.id.toLowerCase(), 2) + \".svg'/>\" + state.text;
    }
    function updateStatusComboboxColor() {
        $('span[title=\"正常\"], span[title=\"Online\"]').parent().css('background-color', 'lightgreen');
        $('span[title=\"维护\"], span[title=\"Maintenance\"]').parent().css('background-color', 'orange');
        $('span[title=\"下架\"], span[title=\"Decommission\"]').parent().css('background-color', 'lightcoral');
        $('span[title=\"敬请期待\"], span[title=\"StayTuned\"]').parent().css('background-color', 'lightblue');
    }
    //
    $(function(){
        $('.header-merchant').unbind();
        $('.footer-timezone').unbind();
        $('.footer-lang').unbind();
        $('.merchant-balance-refresh').unbind();
        var headerOp = $('.header-merchant').select2({
            width: '200px'
        }).on('change', function(e){
            $.post('/api/switch-merchant', {'merchant_code': $(this).val()}, function(res){
                $.pjax.reload('#pjax-container');
            });
        });

        var footerLang = $('.footer-lang').select2({
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function(m) { return m; },
            width: '150px'
        }).on('change', function(e){
            $.post('/api/switch-lang', {'lang': $(this).val()}, function(res){
                location.reload();
            });
        });

        footerTimezone = $('.footer-timezone').select2({
            width: '150px'
        }).on('change', function(e){
            $.post('/api/switch-timezone', {'timezone': $(this).val()}, function(res){
                $.pjax.reload('#pjax-container');
            });
        });

        if ('$mCode' == '') {
            headerOp.trigger('change');
        }
        if ('$timezone' == '') {
            footerTimezone.trigger('change');
        }
        if ('$lang' == '') {
            footerLang.trigger('change');
        }
        $('span[aria-labelledby=\"select2-merchant-container\"]').parents('.select2-container').css('margin-top','8px');

        $('body').on('change', '.grid-select-status, .form-control.status', function(){
            updateStatusComboboxColor();
        });

    });

    $(document).on('pjax:end', function(data, status, xhr, options) {
        if (location.href.indexOf('/edit') != -1 || location.href.indexOf('/create') != -1) {
            $('.header-merchant').prop('disabled', true);
        } else {
            $('.header-merchant').prop('disabled', false);
        }
    });

");

if (!function_exists('micro_timestamp')) {
    /**
     * 毫秒時間戳
     *
     * @return int
     */
    function micro_timestamp(): int {
        return intval(round(microtime(true) * 1000));
    }
}

if (!function_exists('gen_trace_id')) {
    /**
     * 產生注單號
     *
     * @return string
     */
    function gen_trace_id(string $prefix): string {
        return $prefix . date("YmdHis"). rand(1, 100);
    }
}

if (!function_exists('ip_in_range')) {
    /**
     * ip_in_range("192.168.168.14", "192.168.168.0/24")
     * IP範圍檢查
     * @return bool
     */
	function ip_in_range( $ip, $range ) {
		if ( strpos( $range, '/' ) === false ) {
			$range .= '/32';
		}
		// $range is in IP/CIDR format eg 127.0.0.1/24
		list( $range, $netmask ) = explode( '/', $range, 2 );
		$range_decimal = ip2long( $range );
		$ip_decimal = ip2long( $ip );
		$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal = ~ $wildcard_decimal;
		return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
	}
}
