<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="theme-color" media="(prefers-color-scheme: dark)" content="black" />
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <title>新高登棋牌</title>
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
    body {
        margin: 0;            /* Reset default margin */
    }
    iframe {
        display: block;       /* iframes are inline by default */
        background: #000;
        border: none;         /* Reset default border */
        height: 100vh;        /* Viewport-relative units */
        width: 100vw;
    }
  </style>
</head>

<body>
    <iframe src="" frameborder="0" id="game_url"></iframe>

  <!-- JS -->
  <!-- Include CDN JavaScript -->
  <script src="//unpkg.com/tailwindcss-jit-cdn@1.3.0/dist/tailwindcss-jit-cdn.umd.min.js"></script>
  <script src="//code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
  <script src="/vendor/laravel-admin/sweetalert2/dist/sweetalert2.all.min.js"></script>
  <script src="//static.line-scdn.net/liff/edge/versions/2.21.2/sdk.js"></script>
  <script>
    async function postData(url = '', data = {}) {
        // Default options are marked with *
        const response = await fetch(url, {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            mode: 'cors', // no-cors, *cors, same-origin
            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
            credentials: 'same-origin', // include, *same-origin, omit
            headers: {
            'Content-Type': 'application/json'
            // 'Content-Type': 'application/x-www-form-urlencoded',
            },
            redirect: 'follow', // manual, *follow, error
            referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
            body: JSON.stringify(data) // body data type must match "Content-Type" header
        });
        return response.json(); // parses JSON response into native JavaScript objects
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
        if (liff.isLoggedIn()) {
            liff.getProfile()
                .then((profile) => {
                    postData("{{route('gplay.launch')}}", {u_id: profile.userId, m_code: window.location.pathname.split("/")[2]})
                    .then((data) => {
                        if (data.code === "0") {
                            $('#game_url').prop('src', data.url);
                        } else {
                            liff.openWindow({
                                url: data.url,
                                external: false,
                            });
                        }
                    });
                })
                .catch((err) => {
                    console.log("error", err);
                });
        } else {
            liff.login({redirectUri: "{{route('game.gplay',['id'=>$id])}}?liff_id="+myLiffId});
        }
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
