@extends('front.line.layout.base')
@section('content')
{{--  content  --}}
<div class="wrapper">
    <div id="order_list">
        <div class="info">訂閱名單</div>
        <div class="order_box">
            <table>
                <thead>
                    <tr>
                        <td>照片</td>
                        <td>顯示名字</td>
                        <td>性別</td>
                        <td>生日</td>
                        <td>E-mail</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group->subscribes as $user)
                    <tr>
                        <td><img src="{{$user->picture_url}}" alt=""></td>
                        <td>{{$user->display_name}}</td>
                        <td>{{$user->info['gender']}}</td>
                        <td>{{$user->info['birth_date']}}</td>
                        <td>{{$user->info['email']}}</td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
</div>
{{-- end of content --}}
@endsection
{{--  section of css  --}}
@section('css')
<style>
    img {
        width: 50px;
    }
</style>
@endsection
{{--  section of js  --}}
@section('js')
<script>
  $(document).ready(function(){
    $('.tooltipped').tooltip();
  });
</script>
@endsection