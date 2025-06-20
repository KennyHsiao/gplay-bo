<?php

namespace App\Admin\Extensions\Tools;

use Xn\Admin\Admin;
use Xn\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class LineBotPushToolbar extends AbstractTool
{
    protected $options;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    protected function script() {

        return <<<EOT

$('div#import-xls').on('click', function() {
    var self = this,
        url = $(this).data('url');
    $.fancybox.open({
        src  : url,
        type : 'iframe',
        opts : {
            afterShow : function( instance, current ) {
                console.info( 'done!' );
            },
            iframe: {
                css : {
                    width : '80%',
                    height: '95%'
                }
            }
        }
    });
});



EOT;
    }

    public function render()
    {
        Admin::script($this->script());

        $options = $this->options;

        return view('admin.tools.line-bot-push', compact('options'));
    }
}
