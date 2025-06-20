<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="theme-color" media="(prefers-color-scheme: dark)" content="black" />
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <title>GPay</title>
  <script src="//cdn.tailwindcss.com"></script>
  <!-- Font -->
  <link rel="preconnect" href="//fonts.googleapis.com">
  <link rel="preconnect" href="//fonts.gstatic.com" crossorigin>
  <link href="//fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
        -ms-user-select: none;
        -webkit-user-select: auto;
        user-select: none;
    }
  </style>
</head>

<body>
    <form action="{{route('gpay.postReg', ['id' => $id])}}" method="POST">
        <input type="hidden" name="liff_id" value="{{$liff_id}}">
        <input type="hidden" name="user_line_id" value="{{$user_line_id}}">
        <input type="hidden" name="display_name" value="{{$display_name}}">
        <input type="hidden" name="picture_url" value="{{$picture_url}}">
        <input type="hidden" name="status_message" value="{{$status_message}}">
        {{ csrf_field() }}
        <main class="flex justify-center items-center flex-col h-[100vh]">
            <div class="rounded-xl p-8 flex-row flex items-center">
            <div>
                <img class="w-24 h-24 rounded-full mx-aut"
                src="{{$picture_url}}" alt="" width="384" height="512">
            </div>
            <div class="pt-6 space-y-4 ml-[15px]">
                <div class="text-[18px] font-bold">
                {{$display_name}}
                </div>
            </div>
            </div>
            <div class="text-[14px] mb-[10px]">
                @if($errors->has('auth_code'))
                @foreach($errors->get('auth_code') as $message)
                    <label class="control-label" for="inputError"><i class="far fa-times-circle"></i>{{$message}}</label><br>
                @endforeach
                @endif
                <label for="auth_code">{{__('支付密碼')}}</label>
                <input type="text" id="auth_code" name="auth_code">
            </div>

            <div class="w-full flex justify-center items-center">
                <button class="w-[300px] h-[48px] rounded-md bg-indigo-500 text-white" type="submit" >{{__('送出')}}</button>
            </div>
        </main>
    </form>


  <!-- JS -->
  <!-- Include CDN JavaScript -->
  <script src="//unpkg.com/tailwindcss-jit-cdn@1.3.0/dist/tailwindcss-jit-cdn.umd.min.js"></script>
</body>

</html>
