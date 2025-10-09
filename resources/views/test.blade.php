<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Echo Test</title>
</head>
<body>
    <h1>Welcome</h1>
    <div id="log"></div>
 @vite( 'resources/js/app.js')

    <script type="module">
        // 假設你在 Laravel 廣播事件裡的 Channel 是 public 的，例如：
        console.log('dfsdf');
        window.Echo.channel('test')
            .listen('.create', (e) => {
                console.log('收到事件：', e);
                document.getElementById('log').innerText = JSON.stringify(e);
            });
    </script>
</body>
</html>
