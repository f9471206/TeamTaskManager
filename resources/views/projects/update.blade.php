@extends('layouts.app')

@section('title', '編輯專案')

@section('content')
    <div class="max-w-lg mx-auto bg-white p-6 rounded-xl shadow">
        {{-- 預設標題 --}}
        <h1 class="text-2xl font-bold mb-4 text-gray-800" id="project-title">編輯專案 - 載入中...</h1>

        {{-- *** 關鍵修正：將 ID 寫入一個 data 屬性，前端 JS 才能讀取 *** --}}
        {{-- 假設您在渲染頁面的路由中，還是可以拿到 $projectId 變數 --}}
        <form id="create-project-form" class="space-y-4" data-project-id="{{ $project_id ?? '' }}">

            <div>
                <label for="project_name" class="block font-semibold text-gray-700">專案名稱 <span
                        class="text-red-500">*</span></label>
                {{-- 欄位值留空，等待 JS 填充 --}}
                <input id="project_name" name="name" type="text"
                    class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200 disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-300"
                    required>
            </div>

            <div>
                <label for="project_desc" class="block font-semibold text-gray-700">描述</label>
                {{-- 欄位值留空，等待 JS 填充 --}}
                <textarea id="project_desc" name="description"
                    class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200 disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-300"
                    rows="3" placeholder="選填：簡短描述這個專案的用途..."></textarea>
            </div>

            <button type="submit" id="submitButton" disabled
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                儲存變更
            </button>
        </form>
    </div>

    <script type="module">
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector("#create-project-form");
            if (!form) {
                console.error("錯誤：找不到 ID 為 #create-project-form 的表單。");
                return;
            }

            const projectId = form.dataset.projectId;

            if (!projectId) {
                alert("錯誤：無法取得團隊 ID，請確認後端路由傳遞了 projectId 到 View。");
                return;
            }

            const projectNameInput = document.getElementById('project_name');
            const projectDescTextarea = document.getElementById('project_desc');
            const submitButton = document.getElementById('submitButton');
            const projectTitle = document.getElementById('project-title');
            const originalButtonText = submitButton.textContent;
            const interactiveElements = form.querySelectorAll('input, select, textarea, button');

            // --- 核心：GET 資料載入函式 ---
            const fetchProjectData = async () => {
                // 載入時禁用所有元素
                interactiveElements.forEach(element => element.setAttribute('disabled', 'true'));

                try {
                    const res = await window.api.get(`/projects/${projectId}`);

                    if (res.msg === "success" && res.data) {
                        const project = res.data;
                        console.log(project);
                        // 1. 填充表單欄位
                        projectNameInput.value = project.name || '';
                        projectDescTextarea.value = project.description || '';

                        // 2. 更新標題
                        projectTitle.textContent = `編輯專案 - ${project.name}`;

                        // 3. 載入成功，解除禁用
                        interactiveElements.forEach(element => element.removeAttribute('disabled'));

                    } else {
                        alert("載入專案資料失敗：" + (res.message || "未知錯誤"));
                    }
                } catch (err) {
                    console.error("GET 專案資料失敗：", err);
                    alert(err.message || "載入資料失敗，請稍後再試。");
                }
            };

            // 頁面載入後，立即呼叫 GET 函式
            fetchProjectData();



            // --- PUT/PATCH 更新邏輯 ---
            form.addEventListener("submit", async (e) => {
                e.preventDefault();

                if (submitButton.hasAttribute('disabled')) return;

                const name = projectNameInput.value.trim();
                const description = projectDescTextarea.value.trim();

                if (!name) {
                    alert("請輸入團隊名稱");
                    return;
                }

                // 禁用表單
                interactiveElements.forEach(element => element.setAttribute('disabled', 'true'));
                submitButton.textContent = '儲存中...';

                try {
                    const res = await window.api.put(`/projects/${projectId}`, {
                        name,
                        description
                    });

                    if (res.msg === "success") {
                        alert("專案更新成功！");
                        window.location.href = `/projects/${project}`;
                    } else {
                        alert("更新失敗：" + (res.message || "未知錯誤"));
                    }
                } catch (err) {
                    console.error("更新專案失敗：", err);
                    alert(err.message || "更新失敗，請稍後再試");
                } finally {
                    // 解除禁用 
                    if (window.location.href.includes("/teams") === false) {
                        interactiveElements.forEach(element => element.removeAttribute('disabled'));
                        submitButton.textContent = originalButtonText;
                    }
                }
            });
        });
    </script>
@endsection
