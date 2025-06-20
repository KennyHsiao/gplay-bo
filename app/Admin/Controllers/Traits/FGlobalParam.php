<?php

namespace App\Admin\Controllers\Traits;

use App\Models\System\AdminGlobalParam;
use Illuminate\Support\Facades\Storage;

trait FGlobalParam
{
    /**
     * 初始化
     *
     * @param [type] $slug
     * @return void
     */
    public function initEnvParam(Array $slugs) {
        $global_params = [];
        foreach($slugs as $slug) {
            $global_params[$slug] = AdminGlobalParam::where('type', $slug)->orderBy('type')->orderBy('seq')->get();
        }
        session($global_params);
    }
    /**
     * 清除
     *
     * @return void
     */
    public function destroyEnvParam(Array $slugs = null) {
        // clean up session
        foreach($slugs as $slug) {
            session()->forget($slug);
        }
    }
    /**
     * 產出欄位
     *
     * @param [type] $form
     * @param [type] $oldValues
     * @return void
     */
    public function paramFields($slug, $form, $oldValues = []) {
        $global_params = session($slug);
        foreach($global_params as $param) {
            $select_opts = preg_split( '/\r\n|\r|\n/', $param['param_values']);
            $select_opts = array_combine($select_opts, $select_opts);
            switch ($param['param_type']) {
                case 'int':
                    $input = $form->number($param['param_slug'], $param['param_name']);
                    break;
                case 'radio':
                    $input = $form->radio($param['param_slug'], $param['param_name'])->options(static::$radio_option);
                    break;
                case 'checkbox':
                    $input = $form->checkbox($param['param_slug'], $param['param_name'])->options($select_opts);
                    break;
                case 'select':
                    $input = $form->select($param['param_slug'], $param['param_name'])->options($select_opts);
                    break;
                case 'image':
                    $img_url = null;
                    if (isset($oldValues[$param['param_slug']])) {
                        $img_url = Storage::disk('admin')->url($oldValues[$param['param_slug']]);
                    }
                    $input = $form->image($param['param_slug'], $param['param_name'])->move(self::$imagePath)->attribute('data-initial-preview', $img_url);
                    break;
                case 'textarea':
                    $input = $form->textarea($param['param_slug'], $param['param_name']);
                    break;
                default:
                    $input = $form->text($param['param_slug'], $param['param_name']);
                    break;
            }
            if($param['param_required']) {
                $input = $input->rules('required');
            }
            $input = $input->default(isset($oldValues[$param['param_slug']])?$oldValues[$param['param_slug']]:$param['param_default']);
        }
        $form->ignore($global_params->pluck('param_slug')->toArray());
    }
    /**
     * 儲存
     *
     * @param [type] $form
     * @return void
     */
    public function saveParams(Array $slugs, $form) {
        foreach($slugs as $slug) {
            $global_params = session($slug);
            foreach($global_params as $param) {
                $value = request()->get($param['param_slug']);
                // if(empty($value)) continue;
                if ($param['param_type'] == 'image') {
                    $image = request()->all()[$param['param_slug']];
                    $image_path = $this->saveFile($image, 'images', null, true);
                    $form->model()->param()->updateOrCreate(
                        ['param_slug' => $param['param_slug']], ['param_value' => $image_path]);
                } else {
                    $form->model()->param()->updateOrCreate(
                        ['param_slug' => $param['param_slug']], ['param_value' => $value]);
                }
            }
        }
    }

}
