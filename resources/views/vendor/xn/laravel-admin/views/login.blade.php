<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{config('admin.title')}} | {{ trans('admin.login') }}</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  @if(!is_null($favicon = Admin::favicon()))
  <link rel="shortcut icon" href="{{$favicon}}">
  @endif
  <!-- Select2 -->
  <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/select2/select2.min.css") }}">
  <!-- Bootstrap 3.3.5 -->
  <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/fontawesome/css/fontawesome-all.min.css") }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
  <!-- iCheck -->
  <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/iCheck/square/blue.css") }}">
  <!-- toastr -->
  <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/toastr/build/toastr.min.css") }}">
  <style>
      .flag {
          width: 25px;
      }
  </style>
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition login-page" @if(config('admin.login_background_image'))style="background: url({{config('admin.login_background_image')}}) no-repeat;background-size: cover;"@endif>
<div class="login-box">
  <div class="login-logo">
    <a href="{{ admin_url('/') }}"><b>{{config('admin.name')}}</b></a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">{{ trans('admin.login') }}</p>

    <form action="{{ admin_url('auth/login') }}" method="post" autocomplete="off">
      <div class="form-group has-feedback {!! !$errors->has('username') ?: 'has-error' !!}">

        @if($errors->has('username'))
          @foreach($errors->get('username') as $message)
            <label class="control-label" for="inputError"><i class="far fa-times-circle"></i>{{$message}}</label><br>
          @endforeach
        @endif

        <input type="text" class="form-control" placeholder="{{ trans('admin.username') }}" name="username" value="{{ old('username') }}">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">
        @if($errors->has('password'))
        @foreach($errors->get('password') as $message)
            <label class="control-label" for="inputError"><i class="far fa-times-circle"></i>{{$message}}</label><br>
        @endforeach
        @endif

        <input type="password" class="form-control" placeholder="{{ trans('admin.password') }}" name="password">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback auth-captcha {!! !$errors->has('auth_captcha') ?: 'has-error' !!}">
        @if($errors->has('auth_captcha'))
        @foreach($errors->get('auth_captcha') as $message)
            <label class="control-label" for="inputError"><i class="far fa-times-circle"></i>{{$message}}</label><br>
        @endforeach
        @endif
        <div style="position: relative;">
            <input class="form-control" id="auth_captcha" name="auth_captcha"  placeholder="{{ trans('admin.auth_captcha') }}" >
            <div style="position: absolute;top:2px;right:2px;">
                <a href="javascript:void(0)" id="refreshcaptcha">
                    {!! captcha_img(config('admin.auth.captcha_config')) !!}
                </a>
            </div>
        </div>
     </div>
     <div class="form-group has-feedback auth-otp {!! !$errors->has('auth_otp') ?: 'has-error' !!}">
        @if($errors->has('auth_otp'))
        @foreach($errors->get('auth_otp') as $message)
            <label class="control-label" for="inputError"><i class="far fa-times-circle"></i>{{$message}}</label><br>
        @endforeach
        @endif
        <div style="position: relative;">
            <input class="form-control" id="auth_otp" name="auth_otp"  placeholder="{{ trans('admin.auth_otp') }}" >
        </div>
     </div>
      <div class="row">
        @if(config('admin.multi_timezone'))
        <div class="col-xs-6">
            <div class="form-group">
                <select name="timezone" id="timezone" class="form-control">
                    @foreach ($timezones as $idx => $v)
                        @if ($v['timezone'] === $timezone)
                        <option value="{{$v['timezone']}}" selected>{{$v['name']}}</option>
                        @else
                        <option value="{{$v['timezone']}}">{{$v['name']}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        @endif
        @if(config('admin.multi_locale'))
        <div class="col-xs-6">
            <div class="form-group">
                <select name="locale" id="locale" class="form-control">
                    @foreach ($locales as $v => $k)
                        @if ($v === $locale)
                        <option data-id="{{$v}}" value="{{$v}}" selected>{{$k}}</option>
                        @else
                        <option data-id="{{$v}}" value="{{$v}}">{{$k}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        @endif
      </div>

      <div class="row">
        <div class="col-xs-8">
          @if(config('admin.auth.remember'))
          <div class="checkbox icheck">
            <label>
              <input type="checkbox" name="remember" value="1" {{ (!old('username') || old('remember')) ? 'checked' : '' }}>
              {{ trans('admin.remember_me') }}
            </label>
          </div>
          @endif
        </div>
        <!-- /.col -->
        <div class="col-xs-4">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <button type="submit" class="btn btn-primary btn-block btn-flat">{{ trans('admin.login') }}</button>
        </div>
        <!-- /.col -->
      </div>
    </form>

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 2.1.4 -->
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/jQuery/jquery.min.js")}} "></script>
<!-- Bootstrap 3.3.5 -->
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js")}}"></script>
<!-- iCheck -->
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js")}}"></script>
<!-- Select2 -->
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/select2/select2.full.min.js")}}"></script>
<!-- toastr -->
<script src="{{ admin_asset("vendor/laravel-admin/toastr/build/toastr.min.js")}}"></script>
<script>
    $(function () {
        $('.auth-captcha, .auth-otp').hide();
        if(localStorage.authMethod) {
            $('.auth-'+localStorage.authMethod).show();
        }

        if ('{{session('logout_msg')}}') {
            toastr.options = {
                timeOut : 0,
                extendedTimeOut : 100,
                tapToDismiss : true,
                debug : false,
                fadeOut: 10,
                positionClass : "toast-top-center"
            };
            toastr.warning('{{session('logout_msg')}}');
        }

        if ('{{session('auth_fail')}}') {
            toastr.options = {
                timeOut : 0,
                extendedTimeOut : 100,
                tapToDismiss : true,
                debug : false,
                fadeOut: 10,
                positionClass : "toast-top-center"
            };
            toastr.warning('{{session('auth_fail')}}');
        }

        //
        if(localStorage.locale) {
            $('#locale').val(localStorage.locale);
        }
        // $('#timezone').val(localStorage.timezone);
        //
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });

        function right(str, num)
        {
            return str.substring(str.length-num,str.length)
        }
        function format(state) {
            if (!state.id) return state.text; // optgroup
            return "<img class='flag' src='/images/countryflags/" + right(state.id.toLowerCase(), 2) + ".svg'/>" + state.text;
        }
        $('select[name="locale"]').select2({
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function(m) { return m; }
        });
        $('select[name="locale"]').change(function($e){
            //
            localStorage.locale = $(this).val();
            //localStorage.timezone = $('#timezone').val();
            //
            location.href = location.origin + location.pathname + "?locale=" + $(this).val()+"&timezone="+$('#timezone').val();
        });
        $('select[name="timezone"]').select2({});
        // auth method
        $('input[name="username"]').on('blur', function(){
            if (username = $(this).val()) {
                $.get("{{route('user-auth-method', "")}}?account="+ username, function(data){
                    localStorage.authMethod = data.auth_method;
                    $('.auth-captcha, .auth-otp').hide();
                    $('.auth-'+localStorage.authMethod).show();
                });
            }
        });

        $('body').on('click', '#refreshcaptcha', function(){
            $.ajax({
                url: "{{route('refresh-captcha')}}",
                type: 'get',
                dataType: 'html',
                success: function(json) {
                    $('#refreshcaptcha').html(json);
                },
                error: function(data) {
                    alert('Try Again.');
                }
            });
        })
    });
</script>
</body>
</html>
