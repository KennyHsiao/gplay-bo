<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LIFF Tester</title>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        .container {
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <a id="shareTargetPicker" class="waves-effect waves-light btn">測試分享</a>
    </div>
    <!-- JS -->
    <!-- Include CDN JavaScript -->
    <script src="//unpkg.com/vconsole@latest/dist/vconsole.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="//code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="/vendor/laravel-admin/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="//static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script>
        var vConsole = new window.VConsole();
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
                }, 300);
            }).catch((err) => {
                console.log(myLiffId, err.code, err.message);
            });
        }

        function initializeApp(myLiffId) {
            if (liff.isLoggedIn()) {
                liff.getProfile()
                .then((profile) => {
                    // var data = serialize(profile);
                    // console.log(data);
                    profile = profile;
                    swal.fire(profile.displayName);
                    // liff.logout();
                })
                .catch((err) => {
                    console.log("error", err);
                });
            } else {
                liff.login({ redirectUri: location.href });
            }
        }

        $(function(){
            // const urlParams = new URLSearchParams(window.location.search);
            // var token = urlParams.get("liff.state");
            // if (token === undefined || token === null) {
            //     token = urlParams.get("liff_id");
            // } else {
            //     if (token) {
            //         const urlParams2 = new URLSearchParams(token)
            //         token = urlParams2.get("liff_id");
            //     }
            // }
            initializeLiff("1661531671-gOydWoYp");
            $('body').on('click', '#shareTargetPicker', function(){
                liff.ready.then(() => {
                    liff.getProfile()
                    .then((profile) => {
                        liff
                        .shareTargetPicker(
                            [
                            {
                                type: "text",
                                text: "https://liff.line.me/1661531671-gOydWoYp?refCode="+ profile.userId,
                            },
                            ],
                            {
                            isMultiple: true,
                            }
                        )
                        .then(function (res) {
                            if (res) {
                                // succeeded in sending a message through TargetPicker
                                swal.fire(`[${res.status}] Message sent!`);
                            } else {
                                // sending message canceled
                                swal.fire("TargetPicker was closed!");
                            }
                        })
                        .catch(function (error) {
                            // something went wrong before sending a message
                            swal.fire("something wrong happen");
                        });
                    })
                    .catch((err) => {
                        console.log("error", err);
                    });

                });
            });
        });
      </script>
</body>
</html>
