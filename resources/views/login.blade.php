<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登入 / 註冊頁</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 flex items-center justify-center min-h-screen text-white">
    <div class="bg-gray-800 p-8 rounded-xl shadow-lg w-full max-w-sm space-y-6">
        <h1 class="text-xl font-semibold mb-2 text-center">登入 / 註冊</h1>

        <div id="alert-container"></div>

        <!-- 登入表單 -->
        <form id="login-form" class="space-y-4">
            <h2 class="font-medium text-lg">登入</h2>

            <div>
                <label for="login-email" class="text-gray-400 text-sm mb-1 block">電子郵件</label>
                <input type="email" id="login-email" required value="admin@example.com" placeholder="電子郵件"
                    class="w-full p-2 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-400">
            </div>

            <div class="relative">
                <label for="login-password" class="text-gray-400 text-sm mb-1 block">密碼</label>
                <input type="text" id="login-password" required value="Admin" placeholder="密碼"
                    class="w-full p-2 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-400">
                <button type="button" id="toggle-login-password"
                    class="absolute right-2 top-7 text-gray-400 text-sm">顯示/隱藏</button>
            </div>

            <button id="login-btn"
                class="w-full bg-cyan-500 hover:bg-cyan-600 p-2 rounded-md font-semibold text-gray-900">登入</button>
        </form>

        <hr class="border-gray-600">

        <!-- 註冊表單 -->
        <form id="register-form" class="space-y-4">
            <h2 class="font-medium text-lg">註冊</h2>

            <div>
                <label for="register-name" class="text-gray-400 text-sm mb-1 block">名稱</label>
                <input type="text" id="register-name" required value="user123" placeholder="名稱"
                    class="w-full p-2 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-400">
            </div>

            <div>
                <label for="register-email" class="text-gray-400 text-sm mb-1 block">電子郵件</label>
                <input type="email" id="register-email" required value="user123@example.com" placeholder="電子郵件"
                    class="w-full p-2 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-400">
            </div>

            <div class="relative">
                <label for="register-password" class="text-gray-400 text-sm mb-1 block">密碼</label>
                <input type="text" id="register-password" required value="user123" placeholder="密碼"
                    class="w-full p-2 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-400">
                <button type="button" id="toggle-register-password"
                    class="absolute right-2 top-7 text-gray-400 text-sm">顯示/隱藏</button>
            </div>

            <button id="register-btn"
                class="w-full bg-green-500 hover:bg-green-600 p-2 rounded-md font-semibold text-gray-900">註冊</button>
        </form>

        <pre id="response" class="mt-2 bg-gray-700 p-2 rounded-md text-sm text-gray-300">尚未送出</pre>
    </div>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script type="module">
        const responseBox = document.getElementById("response");

        // 登入事件
        document.getElementById("login-btn").addEventListener("click", async (e) => {
            e.preventDefault();
            const email = document.getElementById("login-email").value.trim();
            const password = document.getElementById("login-password").value.trim();

            if (!email || !password) {
                responseBox.textContent = "⚠️ 請輸入帳號與密碼";
                return;
            }

            try {
                const res = await window.api.post('/login', {
                    email,
                    password
                });
                sessionStorage.setItem('api_token', res.data.token);
                sessionStorage.setItem('user', JSON.stringify(res.data.user));
                responseBox.textContent = "✅ 登入成功，正在跳轉...";
                window.location.href = "/";
            } catch (err) {
                console.error("Login error:", err);
                responseBox.textContent = "❌ 登入失敗，請檢查帳號或密碼";
            }
        });

        // 註冊事件
        document.getElementById("register-btn").addEventListener("click", async (e) => {
            e.preventDefault();
            const name = document.getElementById("register-name").value.trim();
            const email = document.getElementById("register-email").value.trim();
            const password = document.getElementById("register-password").value.trim();

            if (!name || !email || !password) {
                responseBox.textContent = "⚠️ 請完整填寫註冊資訊";
                return;
            }

            try {
                const res = await window.api.post('/register', {
                    name,
                    email,
                    password
                });
                console.log("註冊回傳:", res);
                responseBox.textContent = "✅ 註冊成功！您可以直接登入。";
            } catch (err) {
                console.error("Register error:", err);
                responseBox.textContent = "❌ 註冊失敗，請檢查資訊是否正確或帳號是否已存在";
            }
        });

        // 密碼切換
        document.getElementById('toggle-login-password').addEventListener('click', () => {
            const pw = document.getElementById('login-password');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        });
        document.getElementById('toggle-register-password').addEventListener('click', () => {
            const pw = document.getElementById('register-password');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        });
    </script>
</body>

</html>
