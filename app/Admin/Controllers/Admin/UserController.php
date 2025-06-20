<?php

namespace App\Admin\Controllers\Admin;

use App\Admin\Controllers\AdminController;
use App\Models\Platform\Company;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use Xn\Admin\Layout\Content;
use Xn\Admin\Controllers\Traits\Common;

class UserController extends AdminController
{

    use Common;

    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.administrator');
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        $userModel = config('admin.database.users_model');
        $user = (new $userModel)->where('id', $id)->first();

        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
        $qrcode = $google2fa->getQRCodeInline(
            env('APP_NAME') . " ". env('APP_ENV'),
            $user->name,
            $user->google2fa_secret,
            120
        );
        return view('admin::auth.user.qrcode', compact('qrcode'));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $userModel = config('admin.database.users_model');

        $grid = new Grid(new $userModel());

        $grid->column('id', 'ID')->sortable()->hide();
        $grid->column('username', trans('admin.username'));
        $grid->column('name', trans('admin.name'));
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label();
        $grid->column('auth_method', trans('admin.auth_method'))->display(function($value){
            $label = UserController::authMethod()[$value]??$value;
            if ($value !=="otp") {
                return $label;
            }
            $options = [
                "src" => route('admin.auth.users.show', $this->id),
                "type" => "iframe",
                "iframe" => [
                    "css" => [
                        "width" => '320px',
                        "height" => '320px'
                    ]
                ]
            ];

            return "<a data-fancybox data-options='" . json_encode($options) ."' href='javascript:;'>{$label}<i class='fa fa-qrcode'></i></a>";
        });
        $grid->column('agent.name', __('代理'));
        $grid->column('companies', __('商戶'))->pluck('name')->label();
        $grid->column('status', __('admin.status'))->switch(static::switchLocalize());
        // $grid->column('created_at', trans('admin.created_at'));
        // $grid->column('updated_at', trans('admin.updated_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->row->removable == '0') {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }
    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $userModel = config('admin.database.users_model');

        $show = new Show($userModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('username', trans('admin.username'));
        $show->field('name', trans('admin.name'));
        $show->field('roles', trans('admin.roles'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();
        $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $userModel = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $userModel());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');
        $form->hidden('google2fa_secret');
        $form->display('id', 'ID');
        $form->text('username', trans('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->radio('auth_method', trans('admin.auth_method'))->options(static::authMethod());
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);
        $form->switch('status', trans('admin.status'))->states(static::switchLocalize());
        $form->select('company_code', trans('代理'))->options(Company::where('type', 'agent')->pluck('name', 'code'));
        $form->multipleSelect('companies', trans('商戶'))->groups(Company::groups('merchants'));
        $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));

        //$form->multipleSelect('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));
        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = bcrypt($form->password);
            }

            if (empty($form->model()->google2fa_secret)) {
                $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
                $prefix = str_pad(substr($form->username, 0, 9), 10, 'X');
                $form->google2fa_secret = $google2fa->generateSecretKey(16, $prefix);
            }
        });

        return $form;
    }

}
