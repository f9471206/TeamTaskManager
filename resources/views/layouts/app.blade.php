<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '我的 Laravel 專案')</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                <!-- 通知按鈕 -->
                <div class="relative" id="notification-container">
                    <button id="notification-btn" class="hover:text-cyan-400 relative">
                        🔔
                        <span id="notification-count"
                            class="absolute -top-1 -right-2 bg-red-500 text-xs rounded-full px-1 hidden">0</span>
                    </button>
                    <div id="notification-list"
                        class="absolute right-0 mt-2 w-80 bg-white text-gray-800 rounded shadow-lg overflow-hidden z-50 hidden">
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
        document.addEventListener('DOMContentLoaded', async function() {
            const btn = document.getElementById('notification-btn');
            const list = document.getElementById('notification-list');
            const countBadge = document.getElementById('notification-count');

            // 切換下拉顯示
            btn.addEventListener('click', () => {
                list.classList.toggle('hidden');
            });

            // 更新紅點數量
            function updateBadge(notifications) {
                const unread = notifications.filter(n => n.read_at === null).length;
                if (unread > 0) {
                    countBadge.textContent = unread;
                    countBadge.classList.remove('hidden');
                } else {
                    countBadge.classList.add('hidden');
                }
            }

            function renderNotificationList(notifications) {
                list.innerHTML = '';
                notifications.forEach(n => {
                    const div = document.createElement('div');
                    div.className =
                        'flex justify-between items-center px-4 py-2 border-b border-gray-200 hover:bg-gray-100';

                    const messageSpan = document.createElement('span');
                    messageSpan.textContent = n.message;

                    const openBtn = document.createElement('button');
                    openBtn.textContent = '查看通知';
                    openBtn.className =
                        'ml-4 px-2 py-1 bg-cyan-500 text-white rounded hover:bg-cyan-600';
                    openBtn.addEventListener('click', async () => {
                        // 更新已讀
                        await window.api.post(`/notification/${n.id}/read`);

                        // 建立表單 POST
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '/notification';

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content');
                        form.appendChild(csrfInput);

                        // 傳送物件欄位
                        for (const key in n) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = n[key];
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                    });

                    div.appendChild(messageSpan);
                    div.appendChild(openBtn);
                    list.appendChild(div);
                });
            }


            // function：抓取通知 API 並呼叫更新方法
            async function renderNotifications() {
                try {
                    const res = await window.api.get("/notification");
                    const {
                        msg,
                        data: notifications
                    } = res;

                    if (msg === 'success') {
                        updateBadge(notifications);
                        renderNotificationList(notifications);
                    }
                } catch (err) {
                    console.error('抓取通知失敗：', err);
                }
            }

            // 進入頁面時自動渲染通知
            await renderNotifications();


            // 廣播
            const user = JSON.parse(sessionStorage.getItem('user'));
            const userId = user?.id;
            if (userId) {
                window.Echo.private(`user.${userId}`)
                    .listen('.notify', (e) => {
                        const data = e.message;
                        updateBadge(data);
                        renderNotificationList(data);
                    });
            }


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

        });
    </script>
</body>

</html>
