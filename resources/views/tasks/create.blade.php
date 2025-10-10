@extends('layouts.app')

@section('title', '建立新任務')

@section('content')
    <div class="max-w-xl mx-auto p-6 bg-white rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">為專案建立新任務</h1>

        <form id="create-task-form" class="space-y-6">
            {{-- 1. project_id (隱藏欄位) --}}
            {{-- 必須傳遞給後端，並透過 Blade 預先設定值 --}}
            <input type="hidden" name="project_id" id="project_id" value="{{ $project_id ?? '' }}">

            {{-- 2. title (required) --}}
            <div>
                <label for="task_title" class="block font-semibold text-gray-700 mb-1">任務標題 <span
                        class="text-red-500">*</span></label>
                <input id="task_title" name="title" type="text"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                    placeholder="例如：完成登入頁面設計" required>
            </div>

            {{-- 3. description (nullable) --}}
            <div>
                <label for="task_description" class="block font-semibold text-gray-700 mb-1">詳細描述</label>
                <textarea id="task_description" name="description"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                    rows="4" placeholder="選填：描述任務的細節和需求..."></textarea>
            </div>

            {{-- 4. due_date (nullable|date) --}}
            <div>
                <label for="task_due_date" class="block font-semibold text-gray-700 mb-1">截止日期</label>
                {{-- 使用 type="date" 方便使用者選擇 --}}
                <input id="task_due_date" name="due_date" type="date"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500">
            </div>

            <div id="assign-users-list" class="bg-gray-50 p-4 rounded-lg border">
                {{-- 載入中/預設訊息 --}}
                <p class="text-gray-500">正在載入可指派人員...</p>
            </div>

            <button type="submit" id="submitButton"
                class="w-full bg-blue-600 text-white font-semibold px-4 py-3 rounded-lg hover:bg-blue-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                建立任務
            </button>
        </form>
    </div>



    <script type="module">
        document.addEventListener("DOMContentLoaded", async () => {
            const form = document.querySelector("#create-task-form");
            if (!form) return;

            // 取得所有可交互元素 (用於禁用/啟用)
            const interactiveElements = form.querySelectorAll('input, select, textarea, button');
            const submitButton = document.getElementById('submitButton');
            const originalButtonText = submitButton.textContent;

            // 取得隱藏欄位的 project_id
            const projectIdInput = document.getElementById('project_id');
            const projectId = projectIdInput ? projectIdInput.value.trim() : null;

            // 驗證 project_id 是否存在 (這是必須的)
            if (!projectId) {
                alert("系統錯誤：專案 ID 缺失，無法建立任務。");
                // 禁用所有輸入
                interactiveElements.forEach(element => element.setAttribute('disabled', 'true'));
                return;
            }


            // ------------------------------------
            // 載入指派使用者列表
            // ------------------------------------
            const renderAssignUsersList = (users) => {
                const container = document.getElementById('assign-users-list');
                if (!container) return;

                let html = '<p class="font-semibold text-gray-700 mb-2">指派給：</p>';

                users.forEach(user => {
                    // 處理角色顯示
                    const role = user.pivot ? user.pivot.role : 'Member';
                    const roleClass = role === 'owner' ? 'text-red-600' : 'text-blue-600';

                    html += `
                            <label class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="assigned_user_ids[]" value="${user.id}" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <div>
                                    <span class="font-medium text-gray-800">${user.name}</span> 
                                    <span class="text-sm text-gray-500">(${user.email})</span>
                                    <span class="text-xs ${roleClass} ml-2">(${role})</span>
                                </div>
                            </label>
                        `;
                });

                container.innerHTML = html;
            };

            try {
                // 使用正確的 projectId 變數 (ID 值)
                const res = await window.api.get(`/tasks/createTaskAssignUsersList/${projectId}`);

                if (res.msg === "success" && res.data) {
                    renderAssignUsersList(res.data); // 呼叫渲染函式
                } else {
                    console.warn("載入指派人員列表成功，但無資料。");
                }
            } catch (e) {
                console.error("載入指派人員列表失敗：", e);
                // 這裡可以選擇禁用提交按鈕或顯示錯誤訊息
                // alert("無法載入指派人員列表。"); 
            }


            // end




            form.addEventListener("submit", async (e) => {
                e.preventDefault();

                // 收集表單數據
                const title = document.getElementById("task_title").value.trim();
                const description = document.getElementById("task_description").value.trim();
                const dueDate = document.getElementById("task_due_date").value;
                const assignedUserIds = Array.from(
                    form.querySelectorAll('input[name="assigned_user_ids[]"]:checked')
                ).map(checkbox => checkbox.value);


                // 前端基本驗證
                if (!title) {
                    alert("請輸入任務標題");
                    return;
                }

                // 進入提交狀態：禁用表單並更新按鈕文字
                interactiveElements.forEach(element => element.setAttribute('disabled', 'true'));
                submitButton.textContent = '建立中...';

                try {
                    // 發送 POST 請求到 API
                    const res = await window.api.post("/tasks", { // 假設 API 端點是 /tasks
                        project_id: projectId,
                        title: title,
                        description: description,
                        due_date: dueDate || null, // 如果日期為空，傳送 null
                        user_ids: assignedUserIds
                    });

                    if (res.msg === "success") {
                        alert("任務建立成功！");
                        // 導向回該專案的詳細頁面 (假設路由是 /projects/{id})
                        window.location.href = `/project/${projectId}`;
                    } else {
                        alert("建立失敗：" + (res.message || "未知錯誤"));
                    }
                } catch (err) {
                    console.error("任務建立失敗：", err);
                    // 處理驗證失敗或網路錯誤
                    alert(err.message || "建立失敗，請稍後再試");
                } finally {
                    // 解除禁用 (如果沒有跳轉頁面)
                    if (!window.location.href.includes(`/projects/${projectId}`)) {
                        interactiveElements.forEach(element => element.removeAttribute('disabled'));
                        submitButton.textContent = originalButtonText;
                    }
                }
            });
        });
    </script>
@endsection
