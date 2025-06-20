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
    img {
        -webkit-touch-callout: none; /* iOS Safari */
        -webkit-user-select: auto; /* Safari */
        -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
        user-select: none;
        pointer-events: none;
    }
  </style>
</head>

<body>
    @if (empty($account))
    <main class="flex justify-center items-center flex-col h-[100vh]">
        <div class="flex justify-center items-center">
            <img src="/static/loading.gif" alt="">
        </div>
    </main>
    @else
    <main class="flex justify-center items-center flex-col h-[100vh]" >
        <div class="flex justify-center items-center">
        <img src="{{$qr_code??''}}" alt="">
        </div>

        <div class="rounded-xl p-8 flex-row flex items-center">
        <div>
            <img class="w-24 h-24 rounded-full mx-aut"
            src="{{$picture_url??''}}" alt="" width="384" height="512">
        </div>
        <div class="pt-6 space-y-4 ml-[15px]">
            <div class="text-[18px] font-bold">
                {{__('餘額')}}: {{number_format($balance, 2)}}
            </div>
        </div>
        </div>
        <div class="text-[14px] mb-[10px]">
        說明文字，立即儲存，早享受
        </div>

        <div class="w-full flex justify-center items-center">
        <button class="w-[300px] h-[48px] rounded-md bg-indigo-500 text-white" id="btnScan">{{__('支付掃碼')}}</button>
        </div>
    </main>
    @endif

  <!-- JS -->
  <!-- Include CDN JavaScript -->
  <script src="//unpkg.com/tailwindcss-jit-cdn@1.3.0/dist/tailwindcss-jit-cdn.umd.min.js"></script>
  <script src="//code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
  <script src="/vendor/laravel-admin/sweetalert2/dist/sweetalert2.all.min.js"></script>
  <script src="//static.line-scdn.net/liff/edge/versions/2.21.2/sdk.js"></script>
  <script>

    function serialize(obj) {
        var str = [];
        for (var p in obj)
            if (obj.hasOwnProperty(p)) {
                str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
            }
        return str.join("&");
    }

    function initializeLiff(myLiffId) {
        liff.init({
            liffId: myLiffId
        }).then(() => {
            // start to use LIFF's api
            setTimeout(() => {
                initializeApp(myLiffId);
            }, 1000);
        }).catch((err) => {
            console.log(myLiffId, err.code, err.message);
        });
    }

    function initializeApp(myLiffId) {
        if ("" == "{{$account??''}}") {
            if (liff.isLoggedIn()) {
                liff.getProfile()
                .then((profile) => {
                    var data = serialize(profile);
                    location.replace("https://liff.line.me/"+myLiffId+"?liff_id="+myLiffId+"&"+data)
                })
                .catch((err) => {
                    console.log("error", err);
                });
            }
        }

        $('body').on('click', '#btnScan', function(){
            if (liff.isLoggedIn()) {
                liff.scanCodeV2().then((result) => {
                    var data = JSON.parse(atob(result.value));
                    data.from_account = "{{$account??''}}";
                    location.replace("{{route('gpay.pay',['id'=>$id])}}?liff_id="+myLiffId+"&"+serialize(data));
                }).catch((error) => {
                    console.log("error", error);
                });
            }
        });
    }

    $(function(){
        const urlParams = new URLSearchParams(window.location.search);
        var token = urlParams.get("liff.state");
        if (token === undefined || token === null) {
            token = urlParams.get("liff_id");
        } else {
            if (token) {
                const urlParams2 = new URLSearchParams(token)
                token = urlParams2.get("liff_id");
            }
        }
        initializeLiff(token);
    });
  </script>
</body>

</html>
