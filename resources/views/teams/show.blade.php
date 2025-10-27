@extends('layouts.app')

@section('title', '團隊詳細資料')

@section('content')
    <div class="max-w-5xl mx-auto">
        <a href="{{ url('/teams') }}" class="text-blue-600 hover:underline mb-4 inline-block">&larr; 返回團隊列表</a>

        <div id="team-detail" class="bg-white p-6 rounded-xl shadow border border-gray-200">
            <p id="loading-text" class="text-gray-500">載入中...</p>
        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">

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

                // === 產生成員列表 ===
                const memberList = team.members.map(m => `
                    <div class="flex items-center justify-between mb-4 border-b pb-2">
                        <li>${m.name} <span class="text-xs text-gray-500">(${m.role})</span></li>
                        <button 
                            class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition"
                            data-member-id="${m.id}">
                            移除
                        </button>
                    </div>
                `).join("");

                // === 產生專案列表 ===
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

                // === 組合整體 HTML ===
                teamContainer.innerHTML = `
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold mb-2 text-gray-800">${team.team_name}</h1>
                        <a href="/teams/update/${team.team_id}"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                            編輯
                        </a>
                    </div>
                    <p class="text-gray-600 mb-4">${team.description ?? "（無描述）"}</p>

                    <div class="mb-4">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-gray-700">成員：</span>
                            <a href="/teamInvite/${team.team_id}" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                                邀請
                            </a>
                        </div>
                        <ul class="list-disc list-inside mt-4">${memberList}</ul>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-gray-700">專案：</span>
                            <a href="/project/create/${team.team_id}"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                                新增
                            </a>
                        </div>
                        <ul class="list-disc list-inside mt-4">${projectList}</ul>
                    </div>

                    <p class="text-xs text-gray-400 mt-2">
                        建立於：${team.created_at}
                    </p>
                `;

                // === 綁定移除按鈕事件 ===
                document.querySelectorAll('button[data-member-id]').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        const memberId = e.target.getAttribute('data-member-id');

                        console.log(team.team_id);
                        console.log(memberId)

                        if (!confirm('確定要移除此成員嗎？')) return;

                        try {
                            const res = await window.api.delete(
                                `/teams/${team.team_id}/members/${memberId}`);
                            e.target.closest('div').remove();
                            alert('成員已移除');
                        } catch (error) {
                            console.error(error);
                            alert('發生錯誤，請稍後再試');
                        }
                    });
                });

            } catch (err) {
                console.error("載入團隊資料失敗：", err);
                if (loadingText) loadingText.textContent = "載入團隊資料失敗。";
            }
        });
    </script>
@endsection
