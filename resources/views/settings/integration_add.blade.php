@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h2 class="text-2xl font-semibold mb-4">쇼핑몰 계정 추가</h2>
    @if(session('error'))
    <div class="bg-red-100 text-red-600 p-2 rounded mb-2 text-center">
        {{ session('error') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-center">
        {{ session('error') }}
    </div>
@endif

    <form id="integration-form" action="{{ route('integration.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf

        <!-- ✅ 쇼핑몰 선택 -->
        <label class="block text-lg font-semibold mb-2">연동할 쇼핑몰을 선택하세요:</label>
     
        <div class="grid grid-cols-3 md:grid-cols-4 gap-4 mt-4">
            <button type="button" class="shop-card" data-platform="cafe24">
                <div class="shop-banner-container">
                    <img src="{{ asset('images/shops/cafe24_banner.png') }}" alt="Cafe24" class="shop-banner">
                </div>
                <span class="shop-name">Cafe24</span>
            </button>

            <button type="button" class="shop-card" data-platform="smartstore">
                <div class="shop-banner-container">
                    <img src="{{ asset('images/shops/smartstore_banner.png') }}" alt="네이버 스마트스토어" class="shop-banner">
                </div>
                <span class="shop-name">네이버 스마트스토어</span>
            </button>

            <button type="button" class="shop-card" data-platform="coupang">
                <div class="shop-banner-container">
                    <img src="{{ asset('images/shops/coupang_banner.png') }}" alt="쿠팡" class="shop-banner">
                </div>
                <span class="shop-name">쿠팡</span>
            </button>
        </div>

        <input type="hidden" name="platform" id="selected-platform">

        <!-- ✅ 쇼핑몰 ID 입력 -->
        <div class="mt-4">
            <label class="block text-sm font-semibold">쇼핑몰 주소 또는 ID</label>
            <input type="text" name="mall_id" id="mall_id" placeholder="예: mystore" required class="border p-2 rounded w-full">
            <p id="mallError" class="text-red-500 text-sm mt-1 hidden"></p> <!-- 오류 메시지 -->

        </div>

        <!-- ✅ Cafe24 OAuth -->
        <div id="cafe24-fields" class="hidden mt-4 text-center">
            <p class="text-sm text-gray-600">Cafe24는 OAuth 인증이 필요합니다.</p>
            <button type="button" id="cafe24-connect" class="bg-green-500 text-white px-4 py-2 rounded mt-2">Cafe24 연동하기</button>
        </div>

        <!-- ✅ 스마트스토어 & 쿠팡 수동 입력 -->
        <div id="manual-fields" class="hidden mt-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold">API 클라이언트 ID</label>
                    <input type="text" name="client_id" class="border p-2 rounded w-full">
                </div>
                <div>
                    <label class="block text-sm font-semibold">API 클라이언트 시크릿 키</label>
                    <input type="text" name="client_secret" class="border p-2 rounded w-full">
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" id="submit-btn" class="bg-blue-500 text-white px-6 py-2 rounded">연동하기</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let platformInput = document.getElementById("selected-platform");
        let mallIdInput = document.getElementById("mall_id");
        let shopCards = document.querySelectorAll(".shop-card");
        let cafe24Fields = document.getElementById("cafe24-fields");
        let manualFields = document.getElementById("manual-fields");
        let submitBtn = document.getElementById("submit-btn");
        let cafe24Connect = document.getElementById("cafe24-connect");

        shopCards.forEach(card => {
            card.addEventListener("click", function () {
                shopCards.forEach(btn => btn.classList.remove("active"));
                this.classList.add("active");

                platformInput.value = this.getAttribute("data-platform");

                cafe24Fields.classList.add("hidden");
                manualFields.classList.add("hidden");
                submitBtn.classList.remove("hidden");

                if (platformInput.value === "cafe24") {
                    cafe24Fields.classList.remove("hidden");
                    submitBtn.classList.add("hidden");
                } else {
                    manualFields.classList.remove("hidden");
                }
            });
        });

        cafe24Connect.addEventListener("click", function () {
            let mallId = mallIdInput.value.trim();
            if (!mallId) {
                alert("쇼핑몰 ID를 입력하세요.");
                return;
            }
            window.location.href = "{{ route('integration.redirect') }}?mall_id=" + encodeURIComponent(mallId);
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
    let errorMessage = "{{ session('error') }}";
    let mallError = document.getElementById("mallError");

    if (errorMessage) {
        mallError.innerText = errorMessage;
        mallError.classList.remove("hidden");
    }
});
</script>




<!-- ✅ CSS 스타일 추가 -->
<style>
/* ✅ 기본 스타일 */
.shop-card {
    background-color: white;
    border: 2px solid #ddd;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    border-radius: 10px;
    transition: all 0.3s ease;
    width: 100%;
    max-width: 280px;
    height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

/* ✅ 마우스를 올렸을 때 효과 */
.shop-card:hover {
    border-color: #007bff;
    background-color: #eef5ff;
    transform: scale(1.05);
    box-shadow: 0px 4px 8px rgba(0, 123, 255, 0.2);
}

/* ✅ 클릭(선택)된 상태 */
.shop-card.active {
    border-color: #0056b3;
    background-color: #cce5ff;
    box-shadow: 0px 4px 10px rgba(0, 91, 187, 0.2);
    transform: scale(1.08);
}

/* ✅ 쇼핑몰 로고 */
.shop-banner-container {
    width: 100%;
    height: 80px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.shop-banner {
    max-width: 100%;
    max-height: 80px;
    object-fit: contain;
}

/* ✅ 쇼핑몰 이름 */
.shop-name {
    font-size: 16px;
    font-weight: bold;
    margin-top: 8px;
}
</style>

@endsection
