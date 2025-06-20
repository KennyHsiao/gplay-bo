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
    <form action="{{route('gpay.postPay', ['id'=>$id])}}" method="POST" autocomplete="off">
        <input type="hidden" name="liff_id" value="{{$liff_id}}">
        <input type="hidden" name="code" value="{{$code}}">
        <input type="hidden" name="account" value="{{$account}}">
        <input type="hidden" name="from_account" value="{{$from_account}}">
        {{ csrf_field() }}
        <main class="flex justify-center items-center flex-col h-[100vh]">
            <div class="rounded-xl p-8 flex-row flex items-center">
                <div>
                    <label for="">{{__('交易倒數')}}<span id="time">00:00</span></label>
                </div>
            </div>

            <div class="text-[14px] mb-[10px]">
                <img class="w-24 h-24 rounded-full mx-aut"
                src="{{$pic}}" alt="" width="384" height="512">
                <label for="amount">{{__('轉入對象')}} {{$name}}</label>
            </div>
            <div class="text-[14px] mb-[10px]">
                @if($errors->has('amount'))
                @foreach($errors->get('amount') as $message)
                    <label class="control-label" for="inputError"><i class="far fa-times-circle"></i>{{$message}}</label><br>
                @endforeach
                @endif
                <label for="amount">{{__('轉出金額')}}</label>
                <input type="number" id="amount" name="amount" required>
            </div>
            <div class="text-[14px] mb-[10px]">
                @if($errors->has('auth_code'))
                @foreach($errors->get('auth_code') as $message)
                    <label class="control-label" for="inputError"><i class="far fa-times-circle"></i>{{$message}}</label><br>
                @endforeach
                @endif
                <label for="auth_code">{{__('支付密碼')}}</label>
                <input type="password" id="auth_code" name="auth_code" required>
            </div>
            <div class="text-[14px] mb-[10px]">
                {{__('確認轉出點數與對象')}}
            </div>

            <div class="w-full flex justify-center items-center">
            <button class="w-[300px] h-[48px] rounded-md bg-indigo-500 text-white">{{__('確認轉出')}}</button>
            </div>
        </main>
    </form>

  <!-- JS -->
  <!-- Include CDN JavaScript -->
  <script src="//unpkg.com/tailwindcss-jit-cdn@1.3.0/dist/tailwindcss-jit-cdn.umd.min.js"></script>
  <script src="//code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
  <script>
    function startTimer(duration, display, cb) {
        var timer = duration, minutes, seconds;
        var _counter = setInterval(() => {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.text(minutes + ":" + seconds);

            if (--timer < 0) {
                clearInterval(_counter);
                if (typeof(cb) === 'function') {
                    cb();
                }
            }
        }, 1000);
    }
    $(function(){
        var fiveMinutes = 1 * 59,
            display = $('#time');
        startTimer(fiveMinutes, display, function(){
            swal.fire(__('交易逾時，請重新操作'));
        });
    });
  </script>
</body>

</html>
