@extends('layouts.app')

@section('title', '建立新團隊')

@section('content')
    <div class="max-w-lg mx-auto bg-white p-6 rounded-xl shadow">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">建立新團隊</h1>

        <form id="create-team-form" class="space-y-4">
            <div>
                <label for="team_name" class="block font-semibold text-gray-700">團隊名稱 <span
                        class="text-red-500">*</span></label>
                <input id="team_name" name="name" type="text"
                    class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200 disabled:bg-gray-100 disabled:text-gray-500  disabled:border-gray-300"
                    required>
            </div>

            <div>
                <label for="team_desc" class="block font-semibold text-gray-700">描述</label>
                <textarea id="team_desc" name="description"
                    class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200 disabled:bg-gray-100 disabled:text-gray-500  disabled:border-gray-300"
                    rows="3" placeholder="選填：簡短描述這個團隊的用途..."></textarea>
            </div>

            <button type="submit" id="submitButton"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                建立
            </button>
        </form>
    </div>

    <script type="module">
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector("#create-team-form");
            const inputs = form.querySelectorAll('input, select, textarea');
            const submitButton = document.getElementById('submitButton');


            form.addEventListener("submit", async (e) => {
                e.preventDefault();

                const name = document.querySelector("#team_name").value.trim();
                const description = document.querySelector("#team_desc").value.trim();


                if (!name) {
                    alert("請輸入團隊名稱");
                    return;
                }

                inputs.forEach(input => input.setAttribute('disabled', 'true'));

                submitButton.setAttribute('disabled', 'true');

                try {
                    const res = await window.api.post("/teams", {
                        name,
                        description
                    });

                    alert("團隊建立成功！");

                } catch (err) {
                    console.error("建立團隊失敗：", err);
                    alert(err.message || "建立失敗，請稍後再試");
                } finally {
                    inputs.forEach(input => input.removeAttribute('disabled'));
                    submitButton.removeAttribute('disabled');
                }
            });
        });
    </script>
@endsection
