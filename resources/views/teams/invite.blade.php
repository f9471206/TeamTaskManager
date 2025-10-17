@extends('layouts.app')

@section('title', '指派使用者')

@section('content')
    <div class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow mt-6">
        <h2 class="text-2xl font-semibold mb-4">團隊成員指派</h2>

        <form id="assignForm">
            <div id="userList" class="space-y-3 text-gray-700">
                <p id="loading" class="text-gray-500">載入中...</p>
            </div>

            <div class="mt-6 text-right">
                <button type="submit" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">
                    儲存變更
                </button>
            </div>
        </form>

        {{-- 訊息區 --}}
        <p id="message" class="mt-4 text-center text-sm text-gray-600"></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const userList = document.getElementById('userList');
            const loading = document.getElementById('loading');
            const form = document.getElementById('assignForm');
            const message = document.getElementById('message');
            const teamId = @json($teamId);

            // 取得使用者列表
            try {
                const res = await window.api.get(`/teams/${teamId}/all-users`);
                const {
                    data
                } = res;

                if (!data || data.length === 0) {
                    loading.textContent = '目前沒有可指派的使用者。';
                    return;
                }

                loading.remove();

                // 產生使用者清單
                data.forEach(user => {
                    const label = document.createElement('label');
                    label.className =
                        'flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50';

                    const infoDiv = document.createElement('div');
                    infoDiv.innerHTML = `
                        <p class="font-medium text-gray-800">${user.name}</p>
                        <p class="text-sm text-gray-500">${user.email}</p>
                        ${user.is_owner ? '<span class="text-xs text-orange-500">(擁有者)</span>' : ''}
                    `;

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'members[]';
                    checkbox.value = user.id;
                    checkbox.className = 'w-5 h-5 text-cyan-600 rounded';

                    // ✅ 已是成員：打勾並禁用
                    if (user.is_member) {
                        checkbox.checked = true;
                        checkbox.disabled = true;
                        checkbox.classList.add('opacity-50', 'cursor-not-allowed');
                    }

                    label.appendChild(infoDiv);
                    label.appendChild(checkbox);
                    userList.appendChild(label);
                });
            } catch (error) {
                loading.textContent = '載入失敗，請稍後再試。';
                console.error('Error loading user list:', error);
            }


            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                message.textContent = '';

                // 取得所有「可用」且「被勾選」的 checkbox
                const checkedIds = Array.from(document.querySelectorAll(
                        'input[name="members[]"]:checked'))
                    .filter(cb => !cb.disabled) // ⚡ 只取非會員
                    .map(cb => cb.value);

                if (checkedIds.length === 0) {
                    message.textContent = '⚠️ 沒有可新增的使用者。';
                    message.className = 'mt-4 text-center text-sm text-yellow-600';
                    return;
                }

                try {
                    const res = await window.api.post(`/teams/${teamId}/invite`, {
                        user_id: checkedIds
                    });

                    if (res.msg === 'success' || res.message === 'Invitations sent') {
                        message.textContent = '✅ 成功發送邀請！';
                        message.className = 'mt-4 text-center text-sm text-green-600';
                    } else {
                        message.textContent = '⚠️ 發送失敗，請再試一次。';
                        message.className = 'mt-4 text-center text-sm text-red-600';
                    }
                } catch (error) {
                    console.error('Error sending invites:', error);
                    message.textContent = '❌ 伺服器發生錯誤，請稍後再試。';
                    message.className = 'mt-4 text-center text-sm text-red-600';
                }
            });
        });
    </script>
@endsection
