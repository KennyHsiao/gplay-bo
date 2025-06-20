<?php

namespace App\Admin\Controllers\Line;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Facades\Admin;
use Xn\Admin\Layout\Content;
use App\Models\Line\LineBotImageMap;
use App\Models\Line\LineBotImageMapAction;
use App\Admin\Controllers\Traits\Common;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class LINEBotImageMapController extends AdminController
{
    use Common;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'LINE影像地圖';

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        // menu

        $map = LineBotImageMap::find($id);

        $items = [
            'header'      => __($this->title),
            'description' => trans('admin.edit'),
            'map'        => $map,
            'action'      => route("line-imagemap.update", [$id]),
            'image_prf'   => '/',
            'action_method'  =>  method_field('PUT'),
            'menu_options' => $this->menuOptions(),
            '_user_' => Admin::user()
        ];
        return view('admin.line-imagemap.form', $items)->render();
    }

    public function update($id) {
        //
        if (request()->has('_editable')) {
            return $this->form()->update($id);
        }
        //
        $validator = Validator::make(request()->all(), [
            'title' => 'required|max:14',
            'image' => 'sometimes|required|image|mimes:jpeg,png',
            'form.*.status' => 'required',
            'form.*.coords' => 'max:50',
            'form.*.action_type' => 'required_unless:form.*.status,"del"',
            'form.*.action_attr' => 'required_unless:form.*.status,"del"',
            'form.*.action_uri' => 'required_unless:form.*.status,"del"',
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
            $map = LineBotImageMap::find($id);
            $oldImage = $map->image;
            $map->fill($inputs);
            $map->merchant_code = Admin::user()->merchant_code;
            $ratio = 1;
            if(isset($inputs['image'])){
                // 計算圖片寬高
                $map->image_width = 1040;
                $ratio = 1040.0 / $inputs['image_width'];
                $map->image_height = intval($ratio * intval($inputs['image_height']));
                // 移除舊的檔案
                static::DeleteDirectory($oldImage);
                //
                $map->image = $this->saveFile($inputs['image']);
                $path_parts = pathinfo($map->image);
                $dir = $path_parts['filename'];
                $image = 'uploads/' . $map->image;
                // 240px, 300px, 460px, 700px, 1040px.
                mkdir("uploads/imagemap/{$dir}", 0755, true);
                $img = Image::make($image)->resize(240, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save("uploads/imagemap/{$dir}/240");
                $img = Image::make($image)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save("uploads/imagemap/{$dir}/300");
                $img = Image::make($image)->resize(460, null,function ($constraint) {
                    $constraint->aspectRatio();
                })->save("uploads/imagemap/{$dir}/460");
                $img = Image::make($image)->resize(700, null,function ($constraint) {
                    $constraint->aspectRatio();
                })->save("uploads/imagemap/{$dir}/700");
                $img = Image::make($image)->resize(1040, null,function ($constraint) {
                    $constraint->aspectRatio();
                })->save("uploads/imagemap/{$dir}/1040");
                usleep(5000);
                // 移除暫存檔
                unlink($image);
                $map->image = "uploads/imagemap/{$dir}";
            }
            if ($map->save()) {
                foreach(request()->all()['form'] as $item) {
                    // 修正座標
                    $coords = $item['coords'];
                    $coords = explode(",", $coords);
                    $coords_arr = array_map(function($p)use($ratio) {
                        return intval($p * $ratio);
                    }, $coords);
                    $item['coords'] = implode(",", $coords_arr);
                    switch($item['status']) {
                        case 'old':
                            $coord = LineBotImageMapAction::find($item['id']);
                            $coord->fill($item);
                            $coord->save();
                        break;

                        case 'new':
                            $coord = LineBotImageMapAction::create($item);
                            $map->actions()->save($coord);
                        break;

                        case 'del':
                        LineBotImageMapAction::destroy($item['id']);
                        break;
                    }
                }
                DB::commit();
                // return response()->json(['status' => true, 'return' => route("line-menu.edit", [$p_id])], 200);
                return response()->json(['status' => true, 'return' => route("line-imagemap.index")], 200);
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
        $map = new LineBotImageMap;

        $items = [
            'header'      => __($this->title),
            'description' => trans('admin.create'),
            'map'        => $map,
            'action'      => route("line-imagemap.store"),
            'image_prf'   => '/',
            'menu_options' => $this->menuOptions(),
            '_user_' => Admin::user()
        ];
        return view('admin.line-imagemap.form', $items)->render();
    }

    public function store() {
        $validator = Validator::make(request()->all(), [
            'title' => 'required|max:14',
            'image' => 'sometimes|required|image|mimes:jpeg,png',
            'form.*.coords' => 'max:50',
            'form.*.action_type' => 'required',
            'form.*.action_attr' => 'required_with:form.*.coords|max:30',
            'form.*.action_uri' => 'required_with:form.*.coords|max:1000'
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
            $map = new LineBotImageMap;
            $map->fill($inputs);
            $map->merchant_code = Admin::user()->merchant_code;
            $ratio = 1;
            if(isset($inputs['image'])){
                // 計算圖片寬高
                $map->image_width = 1040;
                $ratio = 1040.0 / $inputs['image_width'];
                $map->image_height = intval($ratio * intval($inputs['image_height']));
                #
                $map->image = $this->saveFile($inputs['image']);
                $path_parts = pathinfo($map->image);
                $dir = $path_parts['filename'];
                $image = 'uploads/' . $map->image;
                // 240px, 300px, 460px, 700px, 1040px.
                mkdir("uploads/imagemap/{$dir}", 0755, true);
                $img = Image::make($image)->resize(240, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save("uploads/imagemap/{$dir}/240");
                $img = Image::make($image)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save("uploads/imagemap/{$dir}/300");
                $img = Image::make($image)->resize(460, null,function ($constraint) {
                    $constraint->aspectRatio();
                })->save("uploads/imagemap/{$dir}/460");
                $img = Image::make($image)->resize(700, null,function ($constraint) {
                    $constraint->aspectRatio();
                })->save("uploads/imagemap/{$dir}/700");
                $img = Image::make($image)->resize(1040, null,function ($constraint) {
                    $constraint->aspectRatio();
                })->save("uploads/imagemap/{$dir}/1040");
                usleep(3000);
                // 移除暫存檔
                unlink($image);
                $map->image = "uploads/imagemap/{$dir}";
            }
            if ($map->save()) {

                foreach(request()->all()['form'] as $item) {
                    // 修正座標
                    $coords = $item['coords'];
                    $coords = explode(",", $coords);
                    $coords_arr = array_map(function($p)use($ratio) {
                        return intval($p * $ratio);
                    }, $coords);
                    $item['coords'] = implode(",", $coords_arr);
                    switch($item['status']) {
                        case 'old':
                            $coord = LineBotImageMapAction::find($item['id']);
                            $coord->fill($item);
                            $coord->save();
                        break;

                        case 'new':
                            $coord = LineBotImageMapAction::create($item);
                            $map->actions()->save($coord);
                        break;

                        case 'del':
                        LineBotImageMapAction::destroy($item['id']);
                        break;
                    }
                }
                DB::commit();
                // return response()->json(['status' => true, 'return' => route("line-menu.edit", [$p_id])], 200);
                return response()->json(['status' => true, 'return' => route("line-imagemap.index")], 200);
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
        $grid = new Grid(new LineBotImageMap);

        $grid->column('image', __('圖片'))->display(function($val){
            if(empty($val)) {
                $val = asset("/images/empty-avatar.png");
            } else {
                $val = env('APP_URL')."/{$val}/1040";
            }
            return "<img src=\"{$val}\" class=\"img-thumbnail\" style=\"width:60px;\" onerror=\" this.src='/images/empty-avatar.png' \">";
        });
        $grid->column('title', __("名稱"));
        $grid->column('keywords', __("觸發關鍵字"))->editable();
        $grid->column('created_at', __('admin.created_at'));
        $grid->column('updated_at', __('admin.updated_at'));

        $grid->model()->where(function($q){
            $q->where('merchant_code', Admin::user()->merchant_code);
        })->orderBy('created_at', 'desc');
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('title', __('名稱'));
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
        return Admin::form(LineBotImageMap::class, function (Form $form) {
            // $form->switch('main_menu', '設為主選單')->states(self::$switch_open);
            // $form->switch('init_expand', '自動展開')->states(self::$switch_open);
            $form->hidden('keywords');
            $form->saving(function ($form) {
                \Log::info( $form->model()->id);
            });
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
        $map = LineBotImageMap::find($id);
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

    private function menuOptions() {
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
            // '聯絡我們' => [
            //     '聯絡我們' => '聯絡我們'
            // ],
            // '預約' => [
            //     '所有活動' => '所有活動',
            //     '單一活動' => Admin::user()->appointmentTopics()->where('open', '1')->pluck('title', 'id')->toArray(),
            // ],
            '純文字' => [
                '純文字' => ''
            ],
            '關鍵字' => [
                '關鍵字' => ''
            ],
            '會員' => [
                '會員專區' => '會員專區'
            ],
            // '常見問題' => [
            //     '所有分類' => '常見問題',
            //     '單一分類' => \Admin::user()->faq()->pluck('name', 'name')->toArray(),
            // ],
            '連結' => Admin::user()->hyperlinks()->pluck('url', 'title')->toArray(),
            // '連結' => \Admin::user()->hyperlinks()->select('title', \DB::raw("concat('line://app/', liff_id)as url"))->pluck('url', 'title')->toArray(),
            // '菜單' => \Admin::user()->lineMenu()->pluck('line_menu_id', 'menu_title')->toArray(),
        ];
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
