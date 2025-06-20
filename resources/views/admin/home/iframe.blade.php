@extends('admin::index')

@section('content')

    <section class="content">
        @include('admin::partials.error')
        @include('admin::partials.success')
        @include('admin::partials.exception')
        @include('admin::partials.toastr')
        {{--  content  --}}
        <div class="holds-the-iframe loader">
            <iframe id="inner-iframe" src='javascript:window.location.replace("{{$_GET['url']}}")' frameborder="0" style="height:95vh;width:100%;"></iframe>
        </div>
        {{--  end of content  --}}
        <style>
        .content {
            padding: 0 !important;
        }
        .loader {
            background:url(../images/loader.gif) center center no-repeat;
        }
        </style>
    </section>
    <link rel="stylesheet" href="{{ admin_asset("/vendor/libs/ImageMapEditor/line-rich-menu.css") }}" type="text/css">
    <!--[if gte IE 6]>
        <script language="javascript" type="text/javascript" src="{{ admin_asset("/vendor/libs/ImageMapEditor/ext/excanvas.js") }}"></script>
    <![endif]-->
    <script>
        $(function(){
            setTimeout(function(){
                $('.holds-the-iframe').toggleClass('loader');
            }, 1000);
            
        })
    </script>
@endsection