@extends('layouts.app')

@section('title', '測試')

@section('content')
    <div class="max-w-6xl mx-auto">
        <h1>test</h1>
    </div>

    @vite('resources/js/app.js')

    <script type="module">
        const user = JSON.parse(sessionStorage.getItem('user'));
        const userId = user?.id;

        window.Echo.private(`user.${userId}`)
            .listen('.notify', (e) => {
                console.log('接收到私人通知：', e);
            });
    </script>
@endsection
