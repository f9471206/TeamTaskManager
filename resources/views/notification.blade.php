@extends('layouts.app')

@section('title', '通知頁面')

@section('content')
    <div class="max-w-3xl mx-auto mt-8 p-4 bg-white rounded shadow">
        <h1 class="text-xl font-bold mb-4">通知詳細資訊</h1>

        <!-- 顯示訊息 -->
        <p class="mb-2"><strong>訊息：</strong>{{ $data['message'] }}</p>

        <!-- 顯示建立時間 -->
        <p class="mb-4"><strong>建立時間：</strong>{{ $data['created_at'] }}</p>

        <!-- 同意邀請按鈕 -->
        <button id="accept-btn" class="px-4 py-2 bg-cyan-500 text-white rounded hover:bg-cyan-600">
            同意邀請
        </button>
    </div>

    <script>
        document.getElementById('accept-btn').addEventListener('click', async () => {
            try {
                const res = await window.api.get("{{ $data['link'] }}");
                console.log('同意成功:', res);
                alert('已同意邀請！');
                // 可以選擇跳回列表頁或隱藏按鈕
            } catch (err) {
                console.error('同意失敗:', err);
                alert('同意邀請失敗，請重試。');
            }
        });
    </script>
@endsection
