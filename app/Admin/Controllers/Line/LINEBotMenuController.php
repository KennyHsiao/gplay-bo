<?php

namespace App\Admin\Controllers\Line;

use App\Admin\Controllers\AdminController;
use App\Models\Platform\Company;
use App\Models\Merchant\Hyperlink;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Facades\Admin;
use Xn\Admin\Layout\Content;
use App\Models\Line\LineBotMenu;
use App\Models\Line\LineBotMenuAction;
use App\Admin\Controllers\Traits\Common;
use App\Admin\Controllers\Traits\LINEBotMenuSync;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LINEBotMenuController extends AdminController
{
    use Common, LINEBotMenuSync;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'LINE選單';

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $menu = LineBotMenu::find($id);
        $model = Company::class;
        $items = [
            'header'      => __($this->title),
            'description' => trans('admin.edit'),
            'menu'        => $menu,
            'action'      => route("line-menu.update", [$id]),
            'image_prf'   => asset('uploads').'/',
            'action_method'  =>  method_field('PUT'),
            'menu_options' => [
                '連結' => [],
                'LIFF' => []
            ],
            'line_bot' => $model::where('type', 'merchant')->pluck('name', 'code'),
            '_user_' => Admin::user()
        ];
        return view('admin.line-menu.form', $items)->render();
    }

    public function update($id) {
        //
        if (request()->has('main_menu')||request()->has('init_expand')) {
            return $this->form()->update($id);
        }
        //
        $validator = Validator::make(request()->all(), [
            'menu_title' => 'required|max:14',
            'menu_image' => 'sometimes|required|image|mimes:jpeg,png|max:1000',
            'form.*.status' => 'required',
            'form.*.coords' => 'max:50',
            'form.*.menu_type' => 'required_unless:form.*.status,"del"',
            'form.*.menu_attr' => 'required_unless:form.*.status,"del"',
            'form.*.menu_uri' => 'required_unless:form.*.status,"del"',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        } else if (!$this->checkForm()) {
            $validator->getMessageBag()->add('form', '內容不能空白');
            return response()->json($validator->errors(), 400);
        } else {
            //
            $inputs = request()->all();
            DB::beginTransaction();
            $menu = LineBotMenu::find($id);
            $menu->fill($inputs);
            $menu->merchant_code = $inputs['merchant_code'];
            if(isset($inputs['menu_image'])){
                $menu->menu_image = $this->saveFile($inputs['menu_image']);
            }
            if ($menu->save()) {
                foreach(request()->all()['form'] as $item) {
                    //
                    switch($item['status']) {
                        case 'old':
                            $coord = LineBotMenuAction::find($item['id']);
                            $coord->fill($item);
                            $coord->save();
                        break;

                        case 'new':
                            $coord = LineBotMenuAction::create($item);
                            $menu->actions()->save($coord);
                        break;

                        case 'del':
                        LineBotMenuAction::destroy($item['id']);
                        break;
                    }
                }
                DB::commit();
                // return response()->json(['status' => true, 'return' => route("line-menu.edit", [$p_id])], 200);
                return response()->json(['status' => true, 'return' => route("line-menu.index")], 200);
            }
            DB::rollback();
        }
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create(Content $content)
    {
        $menu = new LineBotMenu;
        $model = Company::class;
        $items = [
            'header'      => __($this->title),
            'description' => trans('admin.create'),
            'menu'        => $menu,
            'action'      => route("line-menu.store"),
            'image_prf'   => asset('uploads').'/',
            'menu_options' => [
                '連結' => [],
                'LIFF' => []
            ],
            'line_bot' => $model::where('type', 'merchant')->pluck('name', 'code'),
            '_user_' => Admin::user()
        ];
        return view('admin.line-menu.form', $items)->render();
    }

    public function store() {
        $validator = Validator::make(request()->all(), [
            'menu_title' => 'required|max:14',
            'menu_image' => 'sometimes|required|image|mimes:jpeg,png|max:1000',
            'form.*.coords' => 'max:50',
            'form.*.menu_type' => 'required',
            'form.*.menu_attr' => 'required_with:form.*.coords|max:255',
            'form.*.menu_uri' => 'required_with:form.*.coords|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        } else if (!$this->checkForm()) {
            $validator->getMessageBag()->add('form', __('內容不能空白'));
            return response()->json($validator->errors(), 400);
        } else {
            //
            $inputs = request()->all();
            DB::beginTransaction();
            $menu = new LineBotMenu;
            $menu->fill($inputs);
            $menu->merchant_code = $inputs['merchant_code'];
            if(isset($inputs['menu_image'])){
                $menu->menu_image = $this->saveFile($inputs['menu_image']);
            }
            if ($menu->save()) {

                foreach(request()->all()['form'] as $item) {
                    //
                    switch($item['status']) {
                        case 'old':
                            $coord = LineBotMenuAction::find($item['id']);
                            $coord->fill($item);
                            $coord->save();
                        break;

                        case 'new':
                            $coord = LineBotMenuAction::create($item);
                            $menu->actions()->save($coord);
                        break;

                        case 'del':
                        LineBotMenuAction::destroy($item['id']);
                        break;
                    }
                }
                DB::commit();
                // return response()->json(['status' => true, 'return' => route("line-menu.edit", [$p_id])], 200);
                return response()->json(['status' => true, 'return' => route("line-menu.index")], 200);
            }
            DB::rollback();
        }

    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new LineBotMenu);

        // $grid->column('id', __('ID'))->sortable();
        $grid->column('merchant.name', __('商戶'));
        $grid->column('main_menu', __('設為主選單'))->switch(self::$switch_open);
        $grid->column('init_expand', __('自動展開'))->switch(self::$switch_open)->display(function($val) use ($grid){
            $disabled = empty($this->line_menu_id) ? '' : 'disabled';
            $val = str_replace('/>', "{$disabled} />", $val);
            return $val;
        });
        $grid->column('menu_image', __('選單圖'))->image(null, 75, 75);
        $grid->column('menu_title', __("名稱"));
        $grid->column('line_menu_id', __("LINE選單ID"));
        $grid->column('created_at', __('admin.created_at'));
        $grid->column('updated_at', __('admin.updated_at'));

        $grid->model()->orderBy('created_at', 'desc');

        // $count = LineBotMenu::where(function($q){
        //     $q->where('merchant_code', Admin::user()->merchant_code)->where('line_menu_id','>','');
        // })->count();

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if (!empty($actions->row['line_menu_id'])) {
                $actions->disableDelete();
                $actions->disableEdit();
            }
            //
            $actions->disableView();
            // Line Menu Upload
            if($actions->row["line_menu_id"] > "") {
                $_url = route("line-menu.delete", [$actions->row["id"]]);
                $script = <<<EOT

$('a#delete').on('click', function() {
var self = this;
swal.fire({
    title: "從LINE移除？",
    allowOutsideClick: false,
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: '取消',
    confirmButtonText: '確認',
}).then(function(result){
    if(result.value) {
        swalDialog("資料處理中");
        $.ajax({
            method: 'post',
            url: $(self).data('src'),
            data: {
                _token:LA.token,
            },
            success: function () {
                swal.close();
                $.pjax.reload('#pjax-container');
                toastr.success('操作成功');
            }
        });
    }
});

});

EOT;
                Admin::script($script);
                $actions->appendHtml('<a id="delete" href="javascript:;" data-src="'.$_url.'" style="color:red;">【從LINE移除】</a>');
//
                $_url = route("line-menu.link", [$actions->row["id"]]);
                $script = <<<EOT
$('a#link').on('click', function() {
var self = this;
swal.fire({
    title: "將此選單設定為已存在會員的選單？",
    allowOutsideClick: false,
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: '取消',
    confirmButtonText: '確認',
}).then(function(result){
    if(result.value) {
        swalDialog("資料處理中");
        $.ajax({
            method: 'post',
            url: $(self).data('src'),
            data: {
                _token:LA.token,
            },
            success: function () {
                swal.close();
                $.pjax.reload('#pjax-container');
                toastr.success('操作成功');
            }
        });
    }
});

});

EOT;
                Admin::script($script);
                $actions->appendHtml('<a id="link" href="javascript:;" data-src="'.$_url.'">【設為入口選單】</a>');
            } else {
                // 上傳到LINE
                $_url = route("line-menu.upload", [$actions->row["id"]]);
                $script = <<<EOT

$('a#upload').on('click', function() {
var self = this;
swal.fire({
    title: "上傳到LINE？",
    allowOutsideClick: false,
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: '取消',
    confirmButtonText: '確認',
}).then(function(result){
    if(result.value) {
        swalDialog("資料處理中");
        $.ajax({
            method: 'post',
            url: $(self).data('src'),
            data: {
                _token:LA.token,
            },
            success: function () {
                swal.close();
                $.pjax.reload('#pjax-container');
                toastr.success('操作成功');
            }
        });
    }
});

});

EOT;
                Admin::script($script);
                // if ($count < 10) {
                    $actions->appendHtml('<a id="upload" href="javascript:;" data-src="'.$_url.'">【上傳到LINE】</a>');
                // }
            }
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
        return Admin::form(LineBotMenu::class, function (Form $form) {
            $form->switch('main_menu', __('設為主選單'))->states(self::$switch_open);
            $form->switch('init_expand', __('自動展開'))->states(self::$switch_open);
            $form->image('menu_image', __('選單圖片'));
            $form->saving(function ($form) {
                \Log::info( $form->model()->id);
            });
        });
    }

    public function menuOptions() {

        // 新增主選單
        // $menus = Admin::user()->lineMenu()->pluck('line_menu_id', 'menu_title')->toArray();
        // $menus['主選單'] = '主選單';
        //
        return [
            // '商品列表' => [
            //     '列表' => ['商品分類', '所有商品'],
            //     '單一分類' => Admin::user()->categories()->pluck('name', 'name')->toArray(),
            //     '商品屬性' => [
            //         '新品' => '新品',
            //         '熱門' => '熱門',
            //         '推薦' => '推薦',
            //     ]
            // ],
            // '文章' => Admin::user()->articles()->pluck('id', 'title')->toArray(),
            // '影像地圖' => Admin::user()->imagemaps()->pluck('id', 'title')->toArray(),
            // '聯絡我們' => [
            //     '聯絡我們' => '聯絡我們'
            // ],
            // '純文字' => [
            //     '純文字' => ''
            // ],
            // '關鍵字' => [
            //     '關鍵字' => ''
            // ],
            // '預約' => [
            //     '所有活動' => '所有活動',
            //     '單一活動' => Admin::user()->appointmentTopics()->where('open', '1')->pluck('title', 'id')->toArray(),
            // ],
            // '會員' => [
            //     '會員專區' => '會員專區'
            // ],
            // '常見問題' => [
            //     '所有分類' => '常見問題',
            //     '單一分類' => Admin::user()->faq()->pluck('name', 'name')->toArray(),
            // ],
            '連結' => Hyperlink::where('merchant_code', strtoupper(request()->input('code')))->pluck('url', 'title')->toArray(),
            'LIFF' => Hyperlink::where('merchant_code', strtoupper(request()->input('code')))
                ->select('title', DB::raw("concat('https://liff.line.me/', liff_id)as url"))
                ->pluck('url', 'title')->toArray(),
            // '菜單' => $menus
        ];
    }
}
