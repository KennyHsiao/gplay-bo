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
  <main class="flex justify-center items-center flex-col h-[100vh]">
    <div class="text-[14px] mb-[10px]">
        <img class="w-24 h-24 rounded-full mx-aut"
        src="/static/success_icon.png" alt="" width="512" height="512">
    </div>
    <div class="text-[14px] mb-[10px]">
        {{__('交易資訊')}}
    </div>
    <div class="text-[14px] mb-[10px]">
        {{__('單號')}}：{{$ret['data']['trace_id']}}<br>
        {{__('時間')}}：{{date('Y-m-d H:i:s', substr($ret['data']['created_time'], 0, 10))}}<br>
        {{__('交易前餘額')}}：{{number_format($ret['data']['before_balance'], 2)}}<br>
        {{__('交易金額')}}：{{number_format($ret['data']['transfer_amount'],2)}}<br>
        {{__('餘額')}}：{{number_format($ret['data']['balance'],2)}}<br>
    </div>
  </main>

  <!-- JS -->
  <!-- Include CDN JavaScript -->
  <script src="//unpkg.com/tailwindcss-jit-cdn@1.3.0/dist/tailwindcss-jit-cdn.umd.min.js"></script>
</body>

</html>
