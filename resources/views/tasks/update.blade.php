@extends('layouts.app')

@section('title', '編輯任務')

@section('content')
    <div class="max-w-xl mx-auto p-6 bg-white rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">編輯任務</h1>

        <form id="edit-task-form" class="space-y-6">
            {{-- 任務 ID 與專案 ID --}}
            <input type="hidden" name="task_id" id="task_id" value="{{ $task_id ?? '' }}">
            <input type="hidden" name="project_id" id="project_id" value="{{ $project_id ?? '' }}">

            {{-- 任務標題 --}}
            <div>
                <label for="task_title" class="block font-semibold text-gray-700 mb-1">任務標題 <span
                        class="text-red-500">*</span></label>
                <input id="task_title" name="title" type="text"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                    placeholder="例如：完成登入頁面設計" required>
            </div>

            {{-- 任務描述 --}}
            <div>
                <label for="task_description" class="block font-semibold text-gray-700 mb-1">詳細描述</label>
                <textarea id="task_description" name="description"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                    rows="4" placeholder="描述任務的細節和需求..."></textarea>
            </div>

            {{-- 截止日期 --}}
            <div>
                <label for="task_due_date" class="block font-semibold text-gray-700 mb-1">截止日期</label>
                <input id="task_due_date" name="due_date" type="date"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500">
            </div>

            {{-- 可指派人員 --}}
            <div id="assign-users-list" class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-gray-500">正在載入可指派人員...</p>
            </div>

            {{-- 任務狀態 --}}
            <div>
                <label for="task_status" class="block font-semibold text-gray-700 mb-1">任務狀態</label>
                <select id="task_status" name="status"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500">
                    <option value="">載入中...</option>
                </select>
            </div>

            <button type="submit" id="submitButton"
                class="w-full bg-blue-600 text-white font-semibold px-4 py-3 rounded-lg hover:bg-blue-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                更新任務
            </button>
        </form>
    </div>

    <script type="module">
        document.addEventListener("DOMContentLoaded", async () => {
            const form = document.querySelector("#edit-task-form");
            const submitButton = document.getElementById('submitButton');
            const originalButtonText = submitButton.textContent;

            const taskId = document.getElementById('task_id').value;
            const projectId = document.getElementById('project_id').value;

            const interactiveElements = form.querySelectorAll('input, select, textarea, button');

            if (!taskId) {
                alert("系統錯誤：任務或專案 ID 缺失。");
                interactiveElements.forEach(el => el.setAttribute('disabled', 'true'));
                return;
            }

            // -------------------------------
            // 載入任務資料
            // -------------------------------
            let currentStatusValue = null;
            try {
                const res = await window.api.get(`/tasks/${taskId}`);
                if (res.msg === "success" && res.data) {
                    const task = res.data;

                    document.getElementById("task_title").value = task.title ?? "";
                    document.getElementById("task_description").value = task.description ?? "";
                    if (task.due_date) {
                        document.getElementById("task_due_date").value = task.due_date.split("T")[0];
                    }

                    // ✅ 取出目前的狀態值（enum.value）
                    currentStatusValue = task.status?.value ?? null;
                } else {
                    alert("無法載入任務資料。");
                    return;
                }
            } catch (err) {
                console.error("載入任務失敗：", err);
                alert("無法載入任務資料。");
                return;
            }

            // -------------------------------
            // 載入任務狀態下拉選單
            // -------------------------------
            await loadStatusOptions(currentStatusValue);
            async function loadStatusOptions(selectedStatus = null) {
                const select = document.getElementById('task_status');

                try {
                    const res = await window.api.get('/tasks/status'); // 後端回傳 Enum 清單
                    if (res.msg === "success" && Array.isArray(res.data)) {
                        select.innerHTML = ''; // 清空原選項

                        res.data.forEach(status => {
                            const option = document.createElement('option');
                            option.value = status.id;
                            option.textContent = status.name;

                            if (selectedStatus !== null && parseInt(selectedStatus) === status.id) {
                                option.selected = true;
                            }

                            select.appendChild(option);
                        });
                    } else {
                        select.innerHTML = '<option value="">無法載入狀態</option>';
                    }
                } catch (err) {
                    console.error('載入狀態選單失敗：', err);
                    select.innerHTML = '<option value="">載入失敗</option>';
                }
            }


            // -------------------------------
            // 載入可指派人員
            // -------------------------------
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
                                <input 
                                    type="checkbox" 
                                    name="assigned_user_ids[]" 
                                    value="${user.id}" 
                                    ${user.assigned ? 'checked' : ''} 
                                    class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
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
                const res = await window.api.get(`/tasks/assignUsersList/${taskId}`);
                renderAssignUsersList(res.data);

            } catch (err) {
                console.error("載入指派人員列表失敗：", err);
            }

            // -------------------------------
            // 表單送出 (更新)
            // -------------------------------
            form.addEventListener("submit", async (e) => {
                e.preventDefault();

                const title = document.getElementById("task_title").value.trim();
                const description = document.getElementById("task_description").value.trim();
                const dueDate = document.getElementById("task_due_date").value;
                const status = document.getElementById("task_status").value;
                const assignedUserIds = Array.from(
                    form.querySelectorAll('input[name="assigned_user_ids[]"]:checked')
                ).map(cb => cb.value);

                if (!title) {
                    alert("請輸入任務標題");
                    return;
                }

                interactiveElements.forEach(el => el.setAttribute('disabled', 'true'));
                submitButton.textContent = '更新中...';

                try {
                    const res = await window.api.put(`/tasks/${taskId}`, {
                        title,
                        description,
                        status: status || null,
                        due_date: dueDate || null,
                        user_ids: assignedUserIds
                    });

                    if (res.msg === "success") {
                        alert("任務更新成功！");
                        window.location.href = `/project/${projectId}`;
                    } else {
                        alert("更新失敗：" + (res.message || "未知錯誤"));
                    }
                } catch (err) {
                    console.error("更新任務失敗：", err);
                    alert(err.message || "更新失敗，請稍後再試");
                } finally {
                    if (!window.location.href.includes(`/project/${projectId}`)) {
                        interactiveElements.forEach(el => el.removeAttribute('disabled'));
                        submitButton.textContent = originalButtonText;
                    }
                }
            });
        });
    </script>
@endsection
