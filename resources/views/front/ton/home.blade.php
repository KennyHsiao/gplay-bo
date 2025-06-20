<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TonWallet</title>
    <script src="//unpkg.com/@tonconnect/ui@latest/dist/tonconnect-ui.min.js"></script>
</head>
<body>

    <div id="ton-connect"></div>

    <script>
        const tonConnectUI = new TON_CONNECT_UI.TonConnectUI({
            manifestUrl: '{{env("APP_URL")}}/tonconnect-manifest.json',
            buttonRootId: 'ton-connect'
        });
        tonConnectUI.uiOptions = {
            twaReturnUrl: '{{env("APP_URL")}}'
        }
    </script>
</body>
</html>
