<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登入頁</title>


    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 flex items-center justify-center min-h-screen text-white">
    <div class="bg-gray-800 p-8 rounded-xl shadow-lg w-full max-w-sm">
        <h1 class="text-xl font-semibold mb-2">登入</h1>

        <div id="alert-container"></div>

        <form id="login-form" class="space-y-4">
            <div>
                <label for="email" class="text-gray-400 text-sm mb-1 block">電子郵件</label>
                <input type="email" id="email" required value="admin@example.com"
                    class="w-full p-2 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-400">
            </div>
            <div>
                <label for="password" class="text-gray-400 text-sm mb-1 block">密碼</label>
                <input type="password" id="password" required value="Admin"
                    class="w-full p-2 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-400">
            </div>
            <button id="create_form"
                class="w-full bg-cyan-500 hover:bg-cyan-600 p-2 rounded-md font-semibold text-gray-900">登入</button>
        </form>

        <pre id="response" class="mt-4 bg-gray-700 p-2 rounded-md text-sm text-gray-300">尚未送出</pre>
    </div>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script type="module">
        document.querySelector("#create_form").addEventListener("click", (e) => {
            e.preventDefault();

            window.api.post('/login', {
                    email: 'admin@example.com',
                    password: 'Admin'
                })
                .then(data => {
                    sessionStorage.setItem('api_token', data.data.token);
                    sessionStorage.setItem('user', JSON.stringify(data.data.user));
                    window.location.href = "/";
                })
                .catch(err => {

                });


        })
    </script>
</body>

</html>
