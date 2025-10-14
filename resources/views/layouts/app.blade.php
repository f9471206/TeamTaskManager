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
            <div class="flex items-center space-x-4" x-data="{ open: false, notifications: [] }">
                <a href="{{ url('/') }}" class="hover:text-cyan-400">首頁</a>
                <a href="{{ url('/teams') }}" class="hover:text-cyan-400">團隊</a>
                <a href="{{ url('/profile') }}" class="hover:text-cyan-400">個人資料</a>

                {{-- 🔔 通知按鈕 --}}
                <div class="relative">
                    <button @click="open = !open" class="relative hover:text-cyan-400">
                        🔔
                        <span x-show="notifications.length > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-xs text-white rounded-full w-5 h-5 flex items-center justify-center"
                            x-text="notifications.length"></span>
                    </button>

                    {{-- 通知下拉清單 --}}
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-64 bg-white text-black rounded-lg shadow-lg overflow-hidden z-50">
                        <template x-if="notifications.length === 0">
                            <div class="p-4 text-gray-500 text-center">目前沒有通知</div>
                        </template>

                        <template x-for="(notify, index) in notifications" :key="index">
                            <div class="border-b last:border-none p-3 hover:bg-gray-100">
                                <p class="text-sm" x-text="notify.token"></p> <!-- 顯示 token -->
                                <a :href="notify.api" class="text-blue-500 text-xs hover:underline">查看</a>
                            </div>
                        </template>
                    </div>
                </div>
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

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/app.js'])
    <script type="module">
        const user = JSON.parse(sessionStorage.getItem('user'));
        const userId = user?.id;

        document.addEventListener("alpine:init", () => {
            Alpine.data("notifyCenter", () => ({
                notifications: [],
                add(notification) {
                    this.notifications.unshift(notification);
                },
            }));
        });

        window.Echo.private(`user.${userId}`)
            .listen('.notify', (e) => {
                console.log(e);
                const nav = document.querySelector('[x-data]');
                if (nav && nav.__x) {
                    nav.__x.$data.add({
                        token: e.token ?? '無內容',
                        api: e.api ?? '#',
                    });
                }
            });


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
                sessionStorage.removeItem("user");
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
