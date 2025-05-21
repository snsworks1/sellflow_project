@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h2 class="text-2xl font-semibold mb-4">쇼핑몰 계정 수정</h2>

    <form action="{{ route('integration.update', $integration->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
    @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold">쇼핑몰 플랫폼</label>
                <input type="text" name="platform" value="{{ $integration->platform }}" readonly class="border p-2 rounded w-full bg-gray-100">
            </div>

            <div>
                <label class="block text-sm font-semibold">쇼핑몰 도메인</label>
                <input type="text" name="mall_id" value="{{ $integration->mall_id }}" readonly class="border p-2 rounded w-full bg-gray-100">
            </div>

            <!-- ✅ Cafe24 & 스마트스토어 전용 필드 -->
         

            <div>
                <label class="block text-sm font-semibold">Access Token</label>
                <input type="text" name="access_token" value="{{ $integration->access_token }}" readonly class="border p-2 rounded w-full bg-gray-100">
            </div>

            <div>
                <label class="block text-sm font-semibold">Refresh Token</label>
                <input type="text" name="refresh_token" value="{{ $integration->refresh_token }}" readonly class="border p-2 rounded w-full bg-gray-100">
            </div>

            <!-- ✅ 쿠팡 전용 필드 -->
            <div class="coupang-fields">
                <label class="block text-sm font-semibold">Vendor ID (쿠팡)</label>
                <input type="text" name="vendor_id" value="{{ $integration->vendor_id }}" class="border p-2 rounded w-full">
            </div>

            <div class="coupang-fields">
                <label class="block text-sm font-semibold">Access Key (쿠팡)</label>
                <input type="text" name="access_key" value="{{ $integration->access_key }}" class="border p-2 rounded w-full">
            </div>

            <div class="coupang-fields">
                <label class="block text-sm font-semibold">Secret Key (쿠팡)</label>
                <input type="text" name="secret_key" value="{{ $integration->secret_key }}" class="border p-2 rounded w-full">
            </div>

         
            <div class="col-span-2">
    <div class="bg-gray-50 p-4 rounded-lg border">
        <h3 class="font-semibold mb-2">리프레시 토큰 만료까지 남은 시간</h3>

        @if ($remainingSeconds > 0)
            @php
                $days = floor($remainingSeconds / 86400);
                $hours = floor(($remainingSeconds % 86400) / 3600);
                $minutes = floor(($remainingSeconds % 3600) / 60);
            @endphp
            <p class="text-gray-700">
                ⏰ <strong>
                    @if ($days > 0)
                        {{ $days }}일 
                    @endif
                    @if ($hours > 0 || $days > 0)
                        {{ $hours }}시간 
                    @endif
                    {{ $minutes }}분 남았습니다.
                </strong>
                <br>
                📅 만료일: <strong>{{ $expirationDate }}</strong>
            </p>
            <p class="text-sm text-gray-500 mt-1">
                (14일간 Cafe24 연동기능 미사용 하여 만료될 경우 하단에 생기는 재연동 버튼을 눌러주세요.)
            </p>
        @else
            <p class="text-red-500 font-semibold">
                ❌ 리프레시 토큰이 만료되었습니다.<br>
                📅 만료일: <strong>{{ $expirationDate }}</strong>
            </p>
            <p class="text-sm text-gray-500 mt-1">
                (14일간 Cafe24 연동기능 미사용 하여 만료 되었습니다. </br> 재연동 버튼을 눌러주세요.)
            </p>
            @if ($refreshTokenExpired)
    <a href="{{ route('integration.reauth', $integration->id) }}" class="btn btn-primary">재연동</a>
@endif
        @endif
    </div>
</div>


        </div>

       



        <div class="flex justify-between mt-6">
            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded">수정 완료</button>
            <button type="button" id="test-integration" class="bg-blue-500 text-white px-6 py-2 rounded">연동 테스트</button>
        </div>

        <div id="test-result" class="mt-4 hidden p-4 rounded bg-gray-100">
            <p id="test-message" class="font-semibold"></p>
        </div>
    </form>
</div>

<!-- ✅ JavaScript 추가 -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let platformInput = document.querySelector("input[name='platform']");
        let cafe24Fields = document.querySelectorAll(".cafe24-fields");
        let coupangFields = document.querySelectorAll(".coupang-fields");

        function updateFieldVisibility() {
            let platform = platformInput.value;

            // 모든 필드를 숨기기
            cafe24Fields.forEach(field => field.style.display = "none");
            coupangFields.forEach(field => field.style.display = "none");

            // 선택된 플랫폼에 맞는 필드만 보이게 설정
            if (platform === "cafe24" || platform === "smartstore") {
                cafe24Fields.forEach(field => field.style.display = "block");
            } else if (platform === "coupang") {
                coupangFields.forEach(field => field.style.display = "block");
            }
        }

        // 페이지 로드 시 필드 업데이트
        updateFieldVisibility();

        // ✅ 연동 테스트 버튼 클릭 시 API 호출
        document.getElementById("test-integration").addEventListener("click", function () {
            let mallId = "{{ $integration->mall_id }}";

            fetch(`/test-integration?mall_id=${mallId}`, { method: "GET" })
            .then(response => response.json())
            .then(data => {
                let resultDiv = document.getElementById("test-result");
                let messageDiv = document.getElementById("test-message");

                resultDiv.classList.remove("hidden");
                messageDiv.textContent = data.message;

                if (data.success) {
                    resultDiv.classList.add("bg-green-100");
                    resultDiv.classList.remove("bg-red-100");
                } else {
                    resultDiv.classList.add("bg-red-100");
                    resultDiv.classList.remove("bg-green-100");
                }
            })
            .catch(error => {
                console.error("연동 테스트 실패:", error);
            });
        });
    });

    function reintegrateMall(mallId) {
        const url = `/settings/integration/reintegrate/${mallId}`;
        window.location.href = url;
    }
</script>

@endsection
