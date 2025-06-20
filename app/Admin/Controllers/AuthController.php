<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\Traits\LINENotifyFunc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Xn\Admin\Controllers\AuthController as BaseAuthController;
use Xn\Admin\Facades\Admin;
use Xn\Admin\Form;

class AuthController extends BaseAuthController
{

    use LINENotifyFunc;

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());

        $form->display('username', trans('admin.username'));
        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->lineNotify('line_notify_token', __('LINE Notify'))->attribute([
            'readonly'=>true,
            'data-callbackurl' => route('my.line-notify.callback', ['username' => Admin::user()->username]),
            'data-cancelurl' => route('my.line-notify.cancel', ['username' => Admin::user()->username]),
            'data-lineclientid' => Admin::user()->notify_client_id
        ]);
        $form->passwordMeter('password', trans('admin.password'))->rules('confirmed|required');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });
        $form->password('secret_key', __('密鑰'))->rules('required');
        $form->setAction(admin_url('auth/setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
            if ($form->secret_key && $form->model()->secret_key != $form->secret_key) {
                $form->secret_key = Hash::make($form->secret_key);
            }
        });

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_url('auth/setting'));
        });

        return $form;
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {

        $this->loginValidator($request->all())->validate();

        $credentials = $request->only([$this->username(), 'password']);
        $remember = $request->get('remember', false);


        if ($request->get('auth_otp', false) && $this->otpAuthValidate($request->only([$this->username(), 'auth_otp'])) === false) {
            return back()->withInput()->withErrors([
                'auth_otp' => __('auth.failed'),
            ]);
        }

        if ($this->guard()->attempt($credentials, $remember)) {
            // check user status
            if ($this->guard()->user()->status != 1) {
                $this->guard()->logout();
                return back()->withInput()->withErrors([
                    'status' => __('auth.user_termination'),
                ]);
            }

            $sessionModel = config('admin.database.sessions_model');
            $session = $sessionModel::where($this->username(), $credentials[$this->username()])->first();
            if ($session) {
                $session->delete();
            }
            $locale = request()->get('locale', config('app.locale'));
            session(['locale'=>$locale]);
            $timezone = request()->get('timezone', config('app.timezone'));
            session(['timezone'=>$timezone]);

            return $this->sendLoginResponse($request);
        }

        return back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }
}
