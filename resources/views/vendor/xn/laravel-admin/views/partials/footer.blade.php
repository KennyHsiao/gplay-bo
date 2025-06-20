<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="pull-right hidden-xs" style="margin-top: -6px;">
        @if(config('admin.show_environment'))
            <strong>Env</strong>&nbsp;&nbsp; {!! config('app.env') !!}
        @endif

        &nbsp;&nbsp;&nbsp;&nbsp;

        @if(config('admin.show_version'))
        <strong>Version</strong>&nbsp;&nbsp; {!! \Xn\Admin\Admin::VERSION !!}
        @endif
        <span>
            @if(config('admin.multi_locale'))
            <select class="footer-locale" name="locale" id="locale">
                @foreach (\Xn\Admin\Helper\XNCache::Locales() as $v => $k)
                    @if ($v === session('locale', 'en'))
                    <option value="{{$v}}" selected>{{$k}}</option>
                    @else
                    <option value="{{$v}}">{{$k}}</option>
                    @endif
                @endforeach
            </select>
            @endif
            @if(config('admin.multi_timezone'))
            <select class="footer-timezone" name="timezone" id="timezone">
                @foreach (\Xn\Admin\Helper\XNCache::Timezones() as $idx => $v)
                    @if ($v['timezone']== session('timezone', config('app.timezone')))
                    <option value="{{$v['timezone']}}" selected>{{$v['name']}}</option>
                    @else
                    <option value="{{$v['timezone']}}">{{$v['name']}}</option>
                    @endif
                @endforeach
            </select>
            @endif
        </span>
    </div>
    <!-- Default to the left -->
    <strong>Powered by <a href="{{config('admin.powered_by.url')}}" target="_blank">{{config('admin.powered_by.title')}}</a></strong>
</footer>
<style>
    .flag {
        width: 25px;
    }
</style>
<script>
    $timezone = "{{session('timezone')}}";
    $locale = "{{session('locale')}}";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
        }
    });

    function right(str, num) {
        return str.substring(str.length-num, str.length)
    }

    function format(state) {
        if (!state.id) return state.text; // optgroup
        return "<img class='flag' src='/images/countryflags/" + right(state.id.toLowerCase(), 2) + ".svg'/>" + state.text
    }
    //
    $(function(){
        $('.footer-timezone').unbind();
        $('.footer-locale').unbind();

        var footerLocale = $('.footer-locale').select2({
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function(m) { return m; },
            width: '150px'
        }).on('change', function(e){
            $.post("{{route('xn.switch-locale')}}", {'locale': $(this).val()}, function(res){
                parent.location.reload();
            });
        });

        footerTimezone = $('.footer-timezone').select2({
            width: '150px'
        }).on('change', function(e){
            $.post("{{route('xn.switch-timezone')}}", {'timezone': $(this).val()}, function(res){
                $.pjax.reload('#pjax-container');
            });
        });

        if ($timezone == '') {
            footerTimezone.trigger('change');
        }
        if ($locale == '') {
            footerLocale.trigger('change');
        }
    });

    $(document).on('pjax:end', function(data, status, xhr, options) {
//
    });

</script>
