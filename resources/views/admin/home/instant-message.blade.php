@extends('admin::index')

@section('content')

    <section class="content">
        @include('admin::partials.error')
        @include('admin::partials.success')
        @include('admin::partials.exception')
        @include('admin::partials.toastr')
        {{--  content  --}}
        <div class="row">
            <div class="col-xs-12">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                    <h3>線上即時客服系統</h3>
                    <p>請登入進行服務</p>
                    </div>
                    <div class="icon">
                    <i class="fa fa-comments"></i>
                    </div>
                    <a href="https://dashboard.tawk.to" class="small-box-footer" target="_blank">
                    登入 <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <!-- ./col -->
        </div>
        <div></div>
        <div class="jumbotron">
            <h1>歡迎使用即時聊天功能</h1>
            <p>色的新急，差益示熱，得上不標果物願看續把叫物的大老、嚴車施了過上業究足他。景長無，本經線話色望心老不以之現化表傳土西同了？示天沒！所離公、作人精學覺動良、說相巴自的非的書而發你團著受燈令根知照那：與家子便有公女命員大小利可正。

足我量修世再入紀名排邊；計中還人時高。

多方前不上術手！玩可空調他工的也係由，少眾同一環會氣了果們的，場不上，我點發給夫節果孩源界發預年配手是是次高。有時條有路造走，人題說，廣了公出？加準家乎頭藝布……面識飯何居是一過備等滿；該不本就動，家黨除死如營分政角我論、麗心商話小這落除不。低舉來，己壓少數子像日我產色直功。治時辦他寫院驗稱得！</p>
            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a></p>
        </div>
        {{--  end of content  --}}
    </section>

@endsection