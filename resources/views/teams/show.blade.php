@extends('layouts.app')

@section('title', '團隊詳細資料')

@section('content')
    <div class="max-w-5xl mx-auto">
        <a href="{{ url('/teams') }}" class="text-blue-600 hover:underline mb-4 inline-block">&larr; 返回團隊列表</a>

        <div id="team-detail" class="bg-white p-6 rounded-xl shadow border border-gray-200">
            <p id="loading-text" class="text-gray-500">載入中...</p>
        </div>

    </div>

    <script type="module">
        document.addEventListener("DOMContentLoaded", async () => {
            const teamContainer = document.querySelector("#team-detail");
            const loadingText = document.querySelector("#loading-text");


            const id = window.location.pathname.split("/").pop();

            try {
                const res = await window.api.get(`/teams/${id}`);
                const {
                    msg,
                    data: team
                } = res;

                if (loadingText) loadingText.remove();

                if (!team) {
                    teamContainer.innerHTML = `<p class="text-gray-500">找不到團隊資料。</p>`;
                    return;
                }

                const memberList = team.members.map(m => `
                    <div class="flex justify-between items-center mb-6">
                        <li>${m.name} <span class="text-xs text-gray-500">(${m.role})</span></li>
                    </div>
                `).join("");

                const projectList = team.projects.length ?
                    team.projects.map(p => `
                        <li>
                            <a href="/project/${p.id}" 
                            class="text-blue-600 hover:text-blue-800 transition">
                                ${p.name} 
                                ${p.description 
                                    ? `<span class="text-gray-600 text-sm ml-2">(${p.description})</span>`
                                    : ''}
                            </a>
                        </li>
                      `).join("") :
                    `<li class="text-gray-400">尚無專案</li>`;

                teamContainer.innerHTML = `
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold mb-2 text-gray-800">${team.team_name}</h1>
                        <a href="{{ url(path: '/teams/update/${team.team_id}') }}"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                            編輯
                        </a>
                    </div>
                    <p class="text-gray-600 mb-4">${team.description ?? "（無描述）"}</p>

                    <div class="mb-4">
                        <span class="font-semibold text-gray-700">成員：</span>
                        <ul class="list-disc list-inside">${memberList}</ul>
                    </div>

                    <div class="mb-4">
                        <span class="font-semibold text-gray-700">專案：</span>
                        <a href="{{ url(path: '/project/create/${team.team_id}') }}"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                            新增
                        </a>
                        <ul class="list-disc list-inside">${projectList}</ul>
                    </div>

                    <p class="text-xs text-gray-400 mt-2">
                        建立於：${team.created_at}
                    </p>
                `;

            } catch (err) {
                console.error("載入團隊資料失敗：", err);
                if (loadingText) loadingText.textContent = "載入團隊資料失敗。";
            }
        });
    </script>
@endsection
