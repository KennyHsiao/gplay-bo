<?php

namespace App\Admin\Controllers\Traits;

use Intervention\Image\Facades\Image;
use Xn\Admin\Facades\Admin;
use Xn\Admin\Form;
use Xn\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Line\LineBotPush;
use App\Models\Line\LineBotPushAction;
use App\Models\Line\LinePushGroup;
use Illuminate\Support\Facades\DB;

trait LineBotPushExt
{
    /**
     * 建立訊息
     *
     * @param Request $request
     * @return void
     */
    public function pushCreate(Request $request, $type) {
        switch ($type) {
            case 'imagemap': return $this->createImagemap(); break;
            default:
                return Admin::content(function (Content $content) use ($type) {

                    $content->header('LINE推播');
                    $content->description('');
                    switch ($type) {
                        case 'text': $content->body($this->createText()); break;
                        case 'image': $content->body($this->createImage()); break;
                        case 'flex': $content->body($this->createFlexMessage()); break;
                    }
                });
            break;
        }

    }

    /**
     * 儲存 建立訊息
     *
     * @param Request $request
     * @return void
     */
    public function pushStore(Request $request, $type) {
        switch ($type) {
            case 'text': return $this->createText()->store(); break;
            case 'image': return $this->createImage()->store(); break;
            case 'imagemap': return $this->storeImagemap(); break;
            case 'flex': return $this->createFlexMessage()->store(); break;
        }
    }

    /**
     * 修改訊息
     *
     * @param Request $request
     * @return void
     */
    public function pushEdit(Request $request, $type, $id) {
        switch ($type) {
            case 'imagemap': return $this->editImagemap($id); break;
            default:
            return Admin::content(function (Content $content) use ($type, $id) {

                $content->header('LINE推播');
                $content->description('');
                switch ($type) {
                    case 'text': $content->body($this->createText()->edit($id)); break;
                    case 'image': $content->body($this->createImage()->edit($id)); break;
                    case 'flex': $content->body($this->createFlexMessage()->edit($id)); break;
                }
            });
        }
    }

    /**
     * 儲存 修改訊息
     *
     * @param Request $request
     * @return void
     */
    public function pushUpdate(Request $request, $type, $id) {
        switch ($type) {
            case 'text': return $this->createText()->update($id); break;
            case 'image': return $this->createImage()->update($id); break;
            case 'imagemap': return $this->updateImagemap($id); break;
            case 'flex': return $this->createFlexMessage()->update($id); break;
        }
    }

    /**
     * 建立純文字訊息
     *
     * @return Form
     */
    protected function createText() {
        return Admin::form(LineBotPush::class, function (Form $form) {
            //
            $newtimestamp = strtotime(date('Y/m/d H:i').' + 5 minute');
            //
            $form->hidden('id');
            $form->hidden('merchant_code')->default(Admin::user()->merchant_code);
            $form->hidden('type')->default('text');
            $form->textarea('message', '訊息')->rules('required');
            $form->inputmask('send_at', '預定發送')->attribute([
                'data-inputmask' => "'mask': '9999/99/99 99:99'"
            ])->icon('fa-clock')->rules('required')
            ->default((empty(static::$seg4) ? date('Y-m-d H:i:s', $newtimestamp): ""));
            // $form->radio('target', '發送對象')->options(self::$push_target)->default('user');
            $form->multipleSelect('targets', '發送對象')->options(LinePushGroup::where('merchant_code', Admin::user()->merchant_code)->pluck('title', 'id'));
            $form->saveing(function ($form) {
                \Log::info( $form->model()->id);
            });
            ### disable button
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
                $tools->disableView();
            });

            if(empty(static::$seg4)) {
                $form->setAction(route('push.create', 'text'));
            } else {
                $form->setAction(route('push.edit', ['text', static::$seg4]));
            }
            $form->saved(function (){
                admin_toastr(trans('admin.save_succeeded'));
                return redirect(route('line-push.index'));
            });
        });
    }

    /**
     * 建立圖片訊息
     *
     * @return Form
     */
    protected function createImage() {
        return Admin::form(LineBotPush::class, function (Form $form) {
            //
            $newtimestamp = strtotime(date('Y/m/d H:i').' + 5 minute');
            //
            $form->hidden('merchant_code')->default(Admin::user()->merchant_code);
            $form->hidden('type')->default('image');
            $form->image('image', '圖片')->resize(1024, null, function ($constraint) {
                $constraint->aspectRatio();
            })->uniqueName()->move(self::$imagePath)->rules('required');
            $form->inputmask('send_at', '預定發送')->attribute([
                'data-inputmask' => "'mask': '9999/99/99 99:99'"
            ])->icon('fa-clock')->rules('required')
            ->default((empty(static::$seg4) ? date('Y-m-d H:i:s', $newtimestamp): ""));
            // $form->radio('target', '發送對象')->options(self::$push_target)->default('user');
            $form->multipleSelect('targets', '發送對象')->options(LinePushGroup::where('merchant_code', Admin::user()->merchant_code)->pluck('title', 'merchant_code'));
            $form->saveing(function ($form) {
                \Log::info( $form->model()->id);
            });
            ### disable button
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
                $tools->disableView();
            });
            if(empty(static::$seg4)) {
                $form->setAction(route('push.create', 'image'));
            } else {
                $form->setAction(route('push.edit', ['image', static::$seg4]));
            }
            $form->saved(function (){
                admin_toastr(trans('admin.save_succeeded'));
                return redirect(route('line-push.index'));
            });
        });
    }

    /**
     * 建立影像地圖
     *
     * @return string
     */

    public function createImagemap()
    {
        $map = new LineBotPush;

        $items = [
            'header'      => 'LINE訊息推播',
            'description' => '建立',
            'map'        => $map,
            'action'      => route("push.create", 'imagemap'),
            'image_prf'   => '/',
            'menu_options' => $this->menuOptions(),
            'push_target' => LinePushGroup::where('merchant_code', Admin::user()->merchant_code)->pluck('title', 'merchant_code')//static::$push_target
        ];
        return view('admin.line-push.form', $items)->render();
    }

    public function storeImagemap() {
        $validator = Validator::make(request()->all(), [
            'title' => 'required|max:14',
            'send_at' => 'required',
            'targets' => 'required',
            'image' => 'sometimes|required|image|mimes:jpeg,png',
            'form.*.coords' => 'max:50',
            'form.*.action_type' => 'required',
            'form.*.action_attr' => 'required_with:form.*.coords|max:30',
            'form.*.action_uri' => 'required_with:form.*.coords|max:1000'
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
            $map = new LineBotPush;
            $map->fill($inputs);
            $map->merchant_code = Admin::user()->merchant_code;
            $map->type = 'imagemap';
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
                usleep(5000);
                // 移除暫存檔
                unlink($image);
                $map->image = "uploads/imagemap/{$dir}";
            }
            if ($map->save()) {
                $map->targets()->sync($inputs['targets']);
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
                            $coord = LineBotPushAction::find($item['id']);
                            $coord->fill($item);
                            $coord->save();
                        break;

                        case 'new':
                            $coord = LineBotPushAction::create($item);
                            $map->actions()->save($coord);
                        break;

                        case 'del':
                        LineBotPushAction::destroy($item['id']);
                        break;
                    }
                }
                DB::commit();
                // return response()->json(['status' => true, 'return' => route("line-menu.edit", [$p_id])], 200);
                return response()->json(['status' => true, 'return' => route("line-push.index")], 200);
            }
            DB::rollback();
        }

    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function editImagemap($id)
    {
        // menu

        $map = LineBotPush::with(['targets'=>function($q){
            $q->select('id');
        }])->find($id);

        $targets = $map['targets']->pluck('id')->toArray();

        $items = [
            'header'      => 'LINE訊息推播',
            'description' => '修改',
            'map'        => $map,
            'action'      => route("push.edit", ['imagemap', $id]),
            'image_prf'   => '/',
            'action_method'  =>  method_field('PUT'),
            'menu_options' => $this->menuOptions(),
            'push_target' => LinePushGroup::where('merchant_code', Admin::user()->merchant_code)->pluck('title', 'merchant_code'),//static::$push_target
            'targets' => $targets
        ];
        return view('admin.line-push.form', $items)->render();
    }

    public function updateImagemap($id) {
        //
        $validator = Validator::make(request()->all(), [
            'title' => 'required|max:14',
            'send_at' => 'required',
            'targets' => 'required',
            'image' => 'sometimes|required|image|mimes:jpeg,png',
            'form.*.status' => 'required',
            'form.*.coords' => 'max:50',
            'form.*.action_type' => 'required_unless:form.*.status,"del"',
            'form.*.action_attr' => 'required_unless:form.*.status,"del"',
            'form.*.action_uri' => 'required_unless:form.*.status,"del"'
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
            $map = LineBotPush::find($id);
            $oldImage = $map->image;
            $map->fill($inputs);
            $map->merchant_code = Admin::user()->id;
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
                usleep(3000);
                // 移除暫存檔
                unlink($image);
                $map->image = "uploads/imagemap/{$dir}";
            }
            if ($map->save()) {
                $map->targets()->sync($inputs['targets']);
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
                            $coord = LineBotPushAction::find($item['id']);
                            $coord->fill($item);
                            $coord->save();
                        break;

                        case 'new':
                            $coord = LineBotPushAction::create($item);
                            $map->actions()->save($coord);
                        break;

                        case 'del':
                            LineBotPushAction::destroy($item['id']);
                        break;
                    }
                }
                DB::commit();
                // return response()->json(['status' => true, 'return' => route("line-menu.edit", [$p_id])], 200);
                return response()->json(['status' => true, 'return' => route("line-push.index")], 200);
            }
            DB::rollback();
        }
    }

    /**
     * 建立卡片訊息
     *
     * @return Form
     */
    protected function createFlexMessage() {
        return Admin::form(LineBotPush::class, function (Form $form) {
            $form->tab('項目', function(Form $form){
                //
                $newtimestamp = strtotime(date('Y/m/d H:i').' + 5 minute');
                //
                $form->hidden('type')->default('flex');
                $form->hidden('merchant_code')->default(Admin::user()->merchant_code);
                $form->image('image', '圖片')->resize(1024, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->uniqueName()->move(self::$imagePath);
                $form->text('title', '名稱')->rules('required|max:40')->attribute('maxlength', 40);
                $form->textarea('message', '描述')->attribute('maxlength', 500);
                $form->inputmask('send_at', '預定發送')->attribute([
                    'data-inputmask' => "'mask': '9999/99/99 99:99'"
                ])->icon('fa-clock')->rules('required')
                ->default((empty(static::$seg4) ? date('Y-m-d H:i:s', $newtimestamp): ""));
                // $form->radio('target', '發送對象')->options(self::$push_target)->default('user');
                $form->multipleSelect('targets', '發送對象')->options(LinePushGroup::where('merchant_code', Admin::user()->merchant_code)->pluck('title', 'merchant_code'));

            })->tab('按鈕', function(Form $form){
                $form->hasMany('actions', null, function (Form\NestedForm $form) {
                    $form->select('action_type', '類型')->options([
                        '文字' => '文字', '圖片' => '圖片', '連結' => '連結'
                    ])->rules('required');
                    $form->text('action_attr', '標籤')->rules('required')->attribute('maxlength', 30);
                    $form->text('action_uri', '連結')->rules(function(){
                        return [
                            "nullable",
                            "required_if:actions.*.action_type,連結",
                            "regex:/^(https:\/\/|http:\/\/|line:\/\/|tel:\/\/)/",
                        ];
                    })->attribute('maxlength', 255)
                    ->help('‘https://’、‘http://’、‘line://’、‘tel://’');
                    $form->image('action_image', '圖片')->rules("sometimes|required_if:actions.*.action_type,圖片")->resize(1024, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->uniqueName()->move(self::$imagePath);
                    $form->textarea('description', '說明')->rules("required_if:actions.*.action_type,文字")->attribute('maxlength', 300);
                    $form->number('seq', '排序');
                });
            });
            ### disable button
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
                $tools->disableView();
            });
            if(empty(static::$seg4)) {
                $form->setAction(route('push.create', 'flex'));
            } else {
                $form->setAction(route('push.edit', ['flex', static::$seg4]));
            }
            $form->saved(function (){
                admin_toastr(trans('admin.save_succeeded'));
                return redirect(route('line-push.index'));
            });
        });
    }

    /**
     * Undocumented function
     *
     * @return array
     */
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
}
