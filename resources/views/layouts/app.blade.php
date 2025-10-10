<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '我的 Laravel 專案')</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

    {{-- Navbar --}}
    <nav class="bg-gray-800 text-white shadow">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="{{ url('/') }}" class="font-bold text-lg">MyApp</a>
            <div class="space-x-4">
                <a href="{{ url('/') }}" class="hover:text-cyan-400">首頁</a>
                <a href="{{ url('/teams') }}" class="hover:text-cyan-400">團隊</a>
                <a href="{{ url('/profile') }}" class="hover:text-cyan-400">個人資料</a>
                <a id="user-logout" href="" class="hover:text-cyan-400">登出</a>
            </div>
        </div>
    </nav>

    {{-- 主要內容 --}}
    <main class="flex-1 container mx-auto px-4 py-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-800 text-white py-4 text-center">
        &copy; {{ date('Y') }} MyApp. All rights reserved.
    </footer>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="module">
        document.querySelector("#user-logout").addEventListener("click", async (e) => {
            e.preventDefault();

            const token = sessionStorage.getItem("api_token");

            if (!token) {
                // 沒有登入狀態，直接導向登入頁
                window.location.href = "/login";
                return;
            }

            try {
                const data = await window.api.post("/logout");
                sessionStorage.removeItem("api_token");
                window.location.href = data.redirect ?? "/login";
            } catch (err) {
                console.error("登出失敗：", err);
                alert(err.message || "登出時發生錯誤");
            }
        });


        document.addEventListener("DOMContentLoaded", async () => {
            const token = sessionStorage.getItem("api_token");

            // 沒 token 就直接導向登入
            if (!token) {
                window.location.href = "/login";
                return;
            }
        });
    </script>
</body>

</html>
