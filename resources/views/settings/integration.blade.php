@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h2 class="text-2xl font-semibold text-center mb-6">쇼핑몰 계정 연동</h2>

    <!-- ✅ 연동 성공/실패 메시지 팝업 -->
    @if(session('success') || session('error'))
    <div id="integration-popup" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <p class="text-lg font-semibold">
                @if(session('success'))
                    ✅ {{ session('success') }}
                @else
                    ❌ {{ session('error') }}
                @endif
            </p>
            <button onclick="closePopup()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">닫기</button>
        </div>
    </div>
    @endif

    <div class="flex justify-center mb-6">
        <a href="{{ route('integration.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">쇼핑몰 계정 추가</a>
    </div>

    <div class="card-container">
        @foreach ($shoppingMalls as $mall)
            <div class="shop-card">
                <div class="shop-banner-container">
                    <img src="{{ asset('images/shops/' . strtolower($mall->platform ?? 'default') . '_banner.png') }}" 
                         alt="{{ $mall->platform ?? '기본 쇼핑몰' }}" class="shop-banner">
                </div>
                <h3 class="shop-name">{{ strtoupper($mall->platform ?? '기본값') }}</h3>
                <p class="shop-domain">{{ $mall->mall_id ?? '도메인 없음' }}</p>
                <div class="flex justify-center gap-4 mt-4">
                    <a href="{{ route('integration.edit', $mall->id) }}" class="btn-edit">수정</a>
                    <form action="{{ route('integration.destroy', $mall->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">삭제</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    function closePopup() {
        document.getElementById("integration-popup").style.display = "none";
    }

    // ✅ 팝업 자동 닫기 (5초 후)
    setTimeout(function() {
        if (document.getElementById("integration-popup")) {
            closePopup();
        }
    }, 5000);
</script>

<style>
/* ✅ 팝업 스타일 */
#integration-popup {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

#integration-popup div {
    background: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

/* ✅ 전체 컨테이너 높이 조정 */
.container {
    max-width: 1200px;
    margin: auto;
    min-height: 80vh; /* 📌 최소 높이 설정 */
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

/* ✅ 카드 리스트 스타일 */
.card-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
}

/* ✅ 개별 카드 스타일 */
.shop-card {
    background-color: white;
    border: 2px solid #ddd;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    border-radius: 10px;
    transition: all 0.3s ease;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 300px;
}

/* ✅ 배너 이미지 컨테이너 */
.shop-banner-container {
    width: 100%;
    height: 80px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: white;
    border-radius: 5px;
}

/* ✅ 배너 이미지 스타일 */
.shop-banner {
    max-width: 100%;
    max-height: 80px;
    object-fit: contain;
}

/* ✅ 쇼핑몰명 스타일 */
.shop-name {
    font-size: 18px;
    font-weight: bold;
    margin-top: 8px;
}

/* ✅ 도메인 스타일 */
.shop-domain {
    font-size: 14px;
    color: #555;
}

/* ✅ 버튼 스타일 */
.btn-edit {
    background-color: #facc15;
    color: black;
    padding: 8px 16px;
    border-radius: 5px;
}

.btn-delete {
    background-color: #dc2626;
    color: white;
    padding: 8px 16px;
    border-radius: 5px;
}
</style>
@endsection

@if(session('error'))
<script>
    window.onload = function() {
        alert("🚨 연동 실패: {{ session('error') }}");
    }
</script>
@endif