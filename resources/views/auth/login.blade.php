<!DOCTYPE html>
<html lang="kr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인</title>
@php
    $viteManifestPath = public_path('build/.vite/manifest.json'); // 📌 `.vite/manifest.json`을 참조

    if (file_exists($viteManifestPath)) {
        $manifest = json_decode(file_get_contents($viteManifestPath), true);

        $cssPath = isset($manifest['resources/css/app.css']) 
            ? 'build/' . $manifest['resources/css/app.css']['file'] 
            : 'build/assets/app.css';

        $jsPath = isset($manifest['resources/js/app.js']) 
            ? 'build/' . $manifest['resources/js/app.js']['file'] 
            : 'build/assets/app.js';
    } else {
        $cssPath = 'build/assets/app.css';
        $jsPath = 'build/assets/app.js';
    }
@endphp

<link rel="stylesheet" href="{{ asset($cssPath) }}">
<script src="{{ asset($jsPath) }}" defer></script>

</head>
<body class="flex items-center justify-center min-h-screen bg-gray-50">

    <div class="w-full max-w-md p-6 bg-white rounded shadow-md">
        <!-- 로고 및 제목 -->
        <div class="flex flex-col justify-center items-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-36 h-36 mb-4">
            <h1 class="text-2xl font-extrabold tracking-tight text-center text-gray-700">로그인</h1>
        </div>

        <!-- 성공 메시지 -->
        @if(session('success'))
            <div class="mb-4 p-4 text-green-800 bg-green-100 border border-green-200 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- 오류 메시지 -->
        @if($errors->any())
            <div class="mb-4 p-4 text-red-800 bg-red-100 border border-red-200 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- 로그인 폼 -->
        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">이메일</label>
                <input type="email" id="email" name="email" class="w-full mt-1 px-4 py-2 border rounded focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">비밀번호</label>
                <input type="password" id="password" name="password" class="w-full mt-1 px-4 py-2 border rounded focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="flex justify-between items-center">
                <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">로그인</button>
                <div class="flex space-x-4">
                    <a href="{{ route('register') }}" class="text-sm text-blue-500 hover:underline">회원가입</a>
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-500 hover:underline">ID/PW 찾기</a>
                </div>
            </div>
        </form>
    </div>

</body>
</html>
