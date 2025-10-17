@extends('layouts.app')

@section('title', '指派使用者')

@section('content')
    <div class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow mt-6">
        <h2 class="text-2xl font-semibold mb-4">任務使用者指派</h2>

        <form id="assignForm" method="POST" action="{{ url("/tasks/assign/$taskId") }}">
            @csrf
            <div id="userList" class="space-y-3 text-gray-700">
                <p id="loading" class="text-gray-500">載入中...</p>
            </div>

            <div class="mt-6 text-right">
                <button type="submit" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">
                    儲存變更
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const userList = document.getElementById('userList');
            const loading = document.getElementById('loading');

            // ✅ 從 Blade 取得 taskId
            const taskId = @json($taskId);

            try {
                const res = await fetch(`/api/tasks/assignUsersList/${taskId}`);
                const json = await res.json();

                if (!json.data || json.data.length === 0) {
                    loading.textContent = '目前沒有可指派的使用者。';
                    return;
                }

                loading.remove();

                json.data.forEach(user => {
                    const label = document.createElement('label');
                    label.className =
                        'flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50';

                    const infoDiv = document.createElement('div');
                    infoDiv.innerHTML = `
                <p class="font-medium text-gray-800">${user.name}</p>
                <p class="text-sm text-gray-500">${user.email}</p>
            `;

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'members[]';
                    checkbox.value = user.id;
                    checkbox.className = 'w-5 h-5 text-cyan-600 rounded';
                    if (user.assigned) checkbox.checked = true;

                    label.appendChild(infoDiv);
                    label.appendChild(checkbox);
                    userList.appendChild(label);
                });
            } catch (error) {
                loading.textContent = '載入失敗，請稍後再試。';
                console.error(error);
            }
        });
    </script>
@endsection
