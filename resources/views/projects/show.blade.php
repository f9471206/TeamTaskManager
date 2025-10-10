@extends('layouts.app')

@section('title', '專案詳細資料')


@section('content')
    <div class="max-w-5xl mx-auto">
        {{-- 返回連結：假設專案列表在 /teams --}}
        <a href="{{ url('/teams') }}" class="text-blue-600 hover:underline mb-4 inline-flex items-center space-x-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>返回團隊列表</span>
        </a>

        <div id="project-detail" class="bg-white p-6 rounded-xl shadow border border-gray-200 min-h-[200px] relative">
            <div id="project-data-container" data-project-id="{{ $id }}"></div>
            <p id="loading-text"
                class="text-gray-500 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                <svg class="animate-spin inline-block h-5 w-5 mr-2 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                載入中...
            </p>

        </div>

    </div>

    <script type="module">
        document.addEventListener("DOMContentLoaded", async () => {
            // *** 修正：確保選擇器與 HTML ID 匹配 (#project-detail) ***
            const projectContainer = document.querySelector("#project-detail");
            const loadingText = document.querySelector("#loading-text");

            const container = document.getElementById('project-data-container');
            const projectId = container ? container.dataset.projectId : null;

            if (projectId) {
                // 現在您就可以使用 projectId 來發送 API 請求了
                // fetchProjectData(projectId); 
                console.log("從 Blade 取得的專案 ID:", projectId);
            }

            // 處理 ID 無效的情況
            if (!projectId || isNaN(projectId)) {
                if (loadingText) loadingText.textContent = "錯誤：無效的專案 ID。";
                return;
            }

            try {
                const res = await window.api.get(`/projects/${projectId}`);

                // *** 修正：解構賦值使用 project 變數名稱 ***
                const {
                    msg,
                    data: project
                } = res;

                if (loadingText) loadingText.remove();

                if (msg !== "success" || !project) {
                    projectContainer.innerHTML = `<p class="text-red-500">載入專案資料失敗或找不到資料。</p>`;
                    return;
                }

                // ------------------------------------
                // 專案資料渲染邏輯
                // ------------------------------------

                // 處理狀態顏色 Class
                const getStatusClasses = (color) => {
                    switch (color) {
                        case 'success':
                            return 'bg-green-100 text-green-800';
                        case 'warning':
                            return 'bg-yellow-100 text-yellow-800';
                        case 'danger':
                            return 'bg-red-100 text-red-800';
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                };

                const statusClasses = getStatusClasses(project.status.color);

                // 格式化日期
                const formattedDate = new Date(project.created_at).toLocaleDateString('zh-TW', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // 處理子任務列表
                const tasksHtml = project.tasks && project.tasks.length > 0 ?
                    project.tasks.map(task => {
                        // 💡 提取使用者名稱的邏輯
                        const userNames = task.users && task.users.length > 0 ?
                            task.users.map(user => user.name).join(', ') :
                            '無指派'; // 或 '-'

                        return `
                                <li class="p-3 border-b last:border-b-0 flex justify-between items-center">
                                    <span class="text-gray-700">${task.title}</span>
                                    <span class="text-gray-700">${task.description ? task.description : '-'}</span>
                                    <span class="text-gray-700">${task.due_date ? task.due_date : '-'}</span>
                                    
                                    <span class="text-gray-700 font-medium">${userNames}</span> 
                                    
                                    <span class="text-xs ${getStatusClasses(task.status.color)} px-2 py-1 rounded-full">${task.status.label}</span>
                                </li> `;
                    }).join('') :
                    '<p class="text-gray-500 italic p-3">目前沒有子任務。</p>';


                // 組合 HTML 模板
                projectContainer.innerHTML = `
                    <div class="flex justify-between items-start mb-6 border-b pb-4">
                        <h1 class="text-4xl font-extrabold text-gray-900">${project.name}</h1>
                        <a href="/projects/edit/${project.id}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition font-medium">
                            編輯專案
                        </a>
                    </div>
                                        
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 mb-1">狀態</p>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium ${statusClasses}">
                                ${project.status.label}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-500 mb-1">所屬團隊 ID</p>
                            <p class="text-lg text-gray-800">${project.team_id}</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-500 mb-1">建立時間</p>
                            <p class="text-lg text-gray-800">${formattedDate}</p>
                        </div>
                        
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-2 border-b pb-1">描述</h2>
                        <p class="text-gray-700 whitespace-pre-wrap min-h-[50px] ${project.description ? '' : 'italic text-gray-500'}">
                            ${project.description || '（無專案描述）'}
                        </p>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-3 border-b pb-1">
                            子任務 (${project.tasks ? project.tasks.length : 0})
                            <a href="/task/create/${project.id}" class="bg-blue-600 text-white px-2 py-1 rounded-lg shadow hover:bg-blue-700 transition">
                                新增
                            </a>
                        </h2>
                        <ul class="divide-y divide-gray-200 border rounded-lg">
                            ${tasksHtml}
                        </ul>
                    </div>
                `;

            } catch (err) {
                console.error("載入專案資料失敗：", err);
                if (loadingText) {
                    loadingText.textContent = "載入專案資料失敗，請檢查網路或 API。";
                    loadingText.classList.add('text-red-500');
                }
            }
        });
    </script>
@endsection
