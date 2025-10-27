@extends('layouts.app')

@section('title', '團隊列表')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">團隊列表</h1>
            <a href="{{ url('/teams/create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                ＋ 新增團隊
            </a>
        </div>

        {{-- 團隊清單 --}}
        <div id="team-list" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6  mb-3">
            <p id="loading-text" class="text-gray-500">載入中...</p>
        </div>

        {{-- 每頁顯示筆數 --}}
        <div class="flex items-center mb-3 space-x-2">
            <span class="text-gray-700">每頁顯示：</span>
            <select id="per-page-select" class="border rounded px-2 py-1">
                <option value="1">1 筆</option>
                <option value="5">5 筆</option>
                <option value="10" selected>10 筆</option>
                <option value="20">20 筆</option>
            </select>
        </div>

        {{-- 顯示筆數資訊 --}}
        <p id="record-info" class="text-gray-500 mb-3"></p>



        {{-- 分頁按鈕 --}}
        <div id="pagination" class="flex justify-center mt-6 space-x-2"></div>
    </div>

    <script type="module">
        document.addEventListener("DOMContentLoaded", () => {
            const teamList = document.querySelector("#team-list");
            const loadingText = document.querySelector("#loading-text");
            const paginationEl = document.querySelector("#pagination");
            const recordInfoEl = document.querySelector("#record-info");
            const perPageSelect = document.querySelector("#per-page-select");

            let currentPage = 1;
            let perPage = parseInt(perPageSelect.value);

            // 當每頁顯示筆數改變時，重新載入資料
            perPageSelect.addEventListener("change", () => {
                perPage = parseInt(perPageSelect.value);
                loadTeams(1); // 回到第一頁
            });

            async function loadTeams(page = 1) {
                currentPage = page;
                if (loadingText) loadingText.textContent = "載入中...";
                try {
                    const res = await window.api.get(`/teams?page=${page}&per_page=${perPage}`);
                    const teams = res.data.items ?? [];
                    const pagination = res.data.pagination ?? null;

                    if (loadingText) loadingText.remove();
                    teamList.innerHTML = "";

                    if (teams.length === 0) {
                        teamList.innerHTML = `<p class="text-gray-500">目前沒有團隊。</p>`;
                        paginationEl.innerHTML = "";
                        recordInfoEl.textContent = "";
                        return;
                    }

                    // 顯示筆數資訊
                    if (pagination) {
                        const from = (pagination.current_page - 1) * pagination.per_page + 1;
                        const to = from + teams.length - 1;
                        recordInfoEl.textContent = `共 ${pagination.total} 筆資料，目前顯示 ${from} ~ ${to}`;
                    }

                    // 顯示團隊卡片
                    teams.forEach(team => {
                        const memberList = team.members.map(m => `
                    <li>${m.name} <span class="text-xs text-gray-500">(${m.role})</span></li>
                `).join("");

                        const projectList = team.projects.length ?
                            team.projects.map(p => `
                        <li>${p.name} ${p.description ? `<span class="text-gray-500 text-sm">- ${p.description}</span>` : ''}</li>
                    `).join("") :
                            `<li class="text-gray-400">尚無專案</li>`;

                        const card = document.createElement("div");
                        card.className =
                            "bg-white rounded-xl shadow p-5 border border-gray-200 hover:shadow-lg transition";

                        card.innerHTML = `
                    <a href="/teams/${team.team_id}" class="block hover:text-cyan-600">
                        <h2 class="text-xl font-semibold mb-2 text-gray-800">${team.team_name}</h2>
                        <p class="text-gray-600 text-sm mb-3">${team.description ?? "（無描述）"}</p>
                        <div class="text-sm mb-3">
                            <span class="font-semibold text-gray-700">成員：</span>
                            <ul class="list-disc list-inside">${memberList}</ul>
                        </div>
                        <div class="text-sm mb-3">
                            <span class="font-semibold text-gray-700">專案：</span>
                            <ul class="list-disc list-inside">${projectList}</ul>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">建立於：${team.created_at}</p>
                    </a>
                `;

                        teamList.appendChild(card);
                    });

                    // 建立頁碼按鈕
                    if (pagination) {
                        paginationEl.innerHTML = "";

                        // 上一頁
                        const prevBtn = document.createElement("button");
                        prevBtn.textContent = "上一頁";
                        prevBtn.disabled = pagination.current_page <= 1;
                        prevBtn.className = "px-3 py-1 bg-gray-200 rounded disabled:opacity-50";
                        prevBtn.onclick = () => loadTeams(pagination.current_page - 1);
                        paginationEl.appendChild(prevBtn);

                        // 動態頁碼按鈕
                        for (let i = 1; i <= pagination.last_page; i++) {
                            const btn = document.createElement("button");
                            btn.textContent = i;
                            btn.className =
                                `px-3 py-1 rounded ${i === pagination.current_page ? "bg-blue-600 text-white" : "bg-gray-200"}`;
                            btn.onclick = () => loadTeams(i);
                            paginationEl.appendChild(btn);
                        }

                        // 下一頁
                        const nextBtn = document.createElement("button");
                        nextBtn.textContent = "下一頁";
                        nextBtn.disabled = pagination.current_page >= pagination.last_page;
                        nextBtn.className = "px-3 py-1 bg-gray-200 rounded disabled:opacity-50";
                        nextBtn.onclick = () => loadTeams(pagination.current_page + 1);
                        paginationEl.appendChild(nextBtn);
                    }

                } catch (err) {
                    console.error("載入團隊資料失敗：", err);
                    if (loadingText) loadingText.textContent = "載入團隊資料失敗。";
                }
            }

            // 初次載入
            loadTeams(currentPage);
        });
    </script>
@endsection
