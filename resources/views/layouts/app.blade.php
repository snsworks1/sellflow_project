<!DOCTYPE html>
<html lang="kr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '관리 대시보드')</title>
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
    @php
    $viteManifestPath = public_path('build/.vite/manifest.json'); // 📌 `.vite/manifest.json` 경로 설정

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

<!-- CSS -->
<link rel="stylesheet" href="{{ asset($cssPath) }}">
<!-- JS -->
<script src="{{ asset($jsPath) }}" defer></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

 

</head>
<script>
window.toggleDarkMode = function () {
    const html = document.documentElement;
    html.classList.toggle('dark');
    localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
    console.log('🌙 다크모드 토글됨');
}
</script>
<body class="bg-white text-black dark:bg-gray-900 dark:bg-gray-900 dark:text-white">

    <!-- 전체 레이아웃 -->
    <div class="flex h-screen flex-col ">
        <!-- 상단 헤더 -->
        <header class="bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-white shadow p-4 flex justify-between items-center">
            
            <!-- 왼쪽 영역 -->
            <div class="flex items-center space-x-6 relative">
                <!-- 쇼핑몰 센터 드롭다운 -->
                <div class="relative">
                    <button
                        id="shoppingMallDropdown"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded focus:outline-none"
                    >
                        쇼핑몰 센터
                    </button>
                    <div
                        id="dropdownMenu"
                        class="hidden absolute mt-2 bg-white text-black shadow-lg rounded-md w-64 z-10"
                    >
                        <ul class="divide-y divide-gray-200">
                            @foreach($shoppingMalls as $mall)
                                <li>
                                    <a
                                        href="{{ $mall['link'] }}"
                                        class="block px-6 py-3 text-sm font-medium hover:bg-gray-100"
                                    >
                                        {{ $mall['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 중앙 정보 -->
            <div class="text-sm text-gray-700 flex items-center space-x-6 dark:text-white">
                <div>남은 주문 수집 건: <span class="text-blue-600 font-bold">10,000</span></div>
                <div>남은 상품 전송 건: <span class="text-blue-600 font-bold">5,000</span></div>
                <div>남은 서비스 일자: <span class="text-blue-600 font-bold">30일</span></div>
            </div>

            <!-- 오른쪽 영역 -->
            <div class="flex items-center space-x-6">
           
            <div class="flex flex-col items-center space-y-1">
        <!-- 업체명 -->
        <div class="text-sm">
            <span class="font-bold text-blue-400">업체명:</span>
            <span class="font-semibold">{{ $businessInfo ? $businessInfo->business_name : '개인회원' }}</span>
        </div>
        <!-- 담당자명 -->
        <div class="text-sm">
            <span class="font-bold text-green-400">담당자명:</span>
            <span class="font-semibold">{{ $businessInfo ? $businessInfo->owner_name : $user->name }}</span>
        </div>
    </div>

        <a href="/mypage" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
            마이페이지
        </a>
        <!-- 헤더 또는 우측 상단에 버튼 배치 -->
        <button onclick="toggleDarkMode()" class="ml-auto px-3 py-2 border rounded text-sm">

    🌗 다크모드 전환
</button>
    </div>
        </header>

        <!-- 메인 레이아웃 -->
        <div class="flex flex-1 ">
            <!-- Sidebar -->
            <aside class="bg-blue-900 text-white w-64 flex flex-col dark:bg-gray-900 dark:text-white">
    <div class="p-6 text-center">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-20 mx-auto mb-4">
        <h2 class="text-xl font-semibold">SellFlow</h2>
    </div>
    <nav class="mt-4 flex-1">
        <ul class="space-y-6">
            <li class="pl-6"><a href="/dashboard" class="text-gray-300 hover:text-white">🏠 홈</a></li>
            <li class="pl-6"><a href="/orders" class="text-gray-300 hover:text-white">🛒 주문 관리</a></li>
            
            <li class="pl-6">
    <a href="#" id="productToggle" class="text-gray-300 hover:text-white flex items-center">
        📦 상품 ▼
    </a>
    <!-- 하위 메뉴 -->
    <ul id="productMenu" class="hidden pl-6 mt-2 space-y-4">
        <li><a href="/products/import" class="text-gray-300 hover:text-white">🛍️ 쇼핑몰 상품 연동</a></li>
    </ul>
</li>
            <!-- 설정 버튼 -->
            <li class="pl-6">
                <a href="#" id="settingsToggle" class="text-gray-300 hover:text-white flex items-center">
                    ⚙️ 설정 ▼
                </a>
                <!-- 하위 메뉴 -->
                <ul id="settingsMenu" class="hidden pl-6 mt-2 space-y-4">
                    <li><a href="/settings/account" class="text-gray-300 hover:text-white">👤 계정관리</a></li>
                    <li><a href="/settings/integration" class="text-gray-300 hover:text-white">🛍️ 쇼핑몰 연동</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <footer class="p-4 text-center text-gray-400 text-sm">
        <p>&copy; {{ date('Y') }} SellFlow</p>
    </footer>
</aside>

            <!-- Main Content -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- 드롭다운 제어 스크립트 -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const dropdownButton = document.getElementById('shoppingMallDropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');

            dropdownButton.addEventListener('click', () => {
                dropdownMenu.classList.toggle('hidden');
            });

            // 클릭 시 드롭다운 닫기
            document.addEventListener('click', (event) => {
                if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        });

        document.getElementById("settingsToggle").addEventListener("click", function(event) {
        event.preventDefault();
        let menu = document.getElementById("settingsMenu");
        menu.classList.toggle("hidden");
    });

    document.getElementById("productToggle").addEventListener("click", function(event) {
        event.preventDefault();
        let menu = document.getElementById("productMenu");
        menu.classList.toggle("hidden");
    });
    </script>

<script>
function toggleDarkMode() {
    const html = document.documentElement;
    html.classList.toggle('dark');
    localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
}

document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark');
    }
});
</script>

    @stack('scripts')
</body>

</html>


