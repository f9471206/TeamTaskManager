@extends('layouts.app')

@section('title', '團隊列表')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">團隊列表</h1>

            <a href="{{ url(path: '/teams/create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                ＋ 新增團隊
            </a>
        </div>

        {{-- 團隊清單 --}}
        <div id="team-list" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <p id="loading-text" class="text-gray-500">載入中...</p>
        </div>
    </div>

    <script type="module">
        document.addEventListener("DOMContentLoaded", async () => {
            const teamList = document.querySelector("#team-list");
            const loadingText = document.querySelector("#loading-text");

            try {
                const res = await window.api.get("/teams");
                const {
                    msg,
                    data
                } = res;

                if (loadingText) loadingText.remove();

                if (!data || data.length === 0) {
                    teamList.innerHTML = `<p class="text-gray-500">目前沒有團隊。</p>`;
                    return;
                }

                data.forEach(team => {
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
                        </a>
                        <p class="text-gray-600 text-sm mb-3">${team.description ?? "（無描述）"}</p>

                        <div class="text-sm mb-3">
                            <span class="font-semibold text-gray-700">成員：</span>
                            <ul class="list-disc list-inside">${memberList}</ul>
                        </div>

                        <div class="text-sm mb-3">
                            <span class="font-semibold text-gray-700">專案：</span>
                            <ul class="list-disc list-inside">${projectList}</ul>
                        </div>

                        <p class="text-xs text-gray-400 mt-2">
                            建立於：${team.created_at}
                        </p>
                        
                    `;

                    teamList.appendChild(card);
                });

            } catch (err) {
                console.error("載入團隊資料失敗：", err);
                if (loadingText) loadingText.textContent = "載入團隊資料失敗。";
            }
        });
    </script>
@endsection
