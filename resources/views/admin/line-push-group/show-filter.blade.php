@extends('front.line.layout.base')
@section('content')
{{--  content  --}}
<div class="wrapper">
    <div id="order_list">
        <div class="info">篩選名單</div>
        <div class="order_box">
            <table>
                <thead>
                    <tr>
                        <td>姓名</td>
                        <td>性別</td>
                        <td>年齡</td>
                        <td>生日</td>
                        <td>縣市別</td>
                        <td>行政區</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($list as $user)
                    <tr>
                        <td>{{$user->last_name.$user->first_name}}</td>
                        <td>{{$user->gender}}</td>
                        <td>{{$user->age}}</td>
                        <td>{{$user->birth_date}}</td>
                        <td>{{$user->county}}</td>
                        <td>{{$user->district}}</td>
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