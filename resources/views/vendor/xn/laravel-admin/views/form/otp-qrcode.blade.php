<div class="{{$viewClass['form-group']}}">
    <label class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        <div class="box box-solid box-default no-margin">
            <!-- /.box-header -->
            <div class="box-body">
                <img class="otp-qrcode" src="{!! $value !!}" alt="">
            </div><!-- /.box-body -->
        </div>

        @include('admin::form.help-block')

    </div>
</div>

<style>
    .otp-qrcode {
        width: 150px;
    }
</style>
