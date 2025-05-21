@extends('layouts.app')

@section('title', '대시보드')

@section('content')
<div class="grid grid-cols-4 gap-4 mb-6">
    <!-- 신규주문 -->
    <div class="bg-blue-500 text-white p-4 rounded-lg shadow-md">
        <h2 class="text-sm font-semibold">신규주문</h2>
        <p class="text-3xl font-bold mt-2">2100</p>
        <small class="text-white/70">+14%</small>
    </div>
    <!-- 출고대기 -->
    <div class="bg-green-500 text-white p-4 rounded-lg shadow-md">
        <h2 class="text-sm font-semibold">출고대기</h2>
        <p class="text-3xl font-bold mt-2">1500</p>
        <small class="text-white/70">+21%</small>
    </div>
    <!-- 반품/교환/취소 -->
    <div class="bg-purple-500 text-white p-4 rounded-lg shadow-md">
        <h2 class="text-sm font-semibold">반품/교환/취소</h2>
        <p class="text-3xl font-bold mt-2">320</p>
        <small class="text-white/70">+8%</small>
    </div>
    <!-- 배송중 -->
    <div class="bg-red-500 text-white p-4 rounded-lg shadow-md">
        <h2 class="text-sm font-semibold">배송중</h2>
        <p class="text-3xl font-bold mt-2">25</p>
        <small class="text-white/70">-5%</small>
    </div>
</div>

<!-- 차트 섹션 -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4">주문 현황</h3>
    <!-- Canvas 크기 설정 -->
    <div class="w-full" style="height: 300px;">
        <canvas id="lineChart" style="max-height: 100%; max-width: 100%;"></canvas>
    </div>
</div>


<div class="grid grid-cols-4 gap-6 mt-8">
    <!-- 상품 현황 -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">상품 현황</h3>
            <button class="text-sm text-blue-500 hover:underline">전체 기간 기준 ↻</button>
        </div>
        <ul class="space-y-2">
            <li class="flex justify-between">
                <span>판매중</span>
                <span class="font-bold text-gray-800">11,287</span>
            </li>
            <li class="flex justify-between">
                <span>종료대기</span>
                <span class="font-bold text-gray-800">63</span>
            </li>
            <li class="flex justify-between">
                <span>반려</span>
                <span class="font-bold text-gray-800">8</span>
            </li>
            <li class="flex justify-between">
                <span>일시품절</span>
                <span class="font-bold text-gray-800">1,174</span>
            </li>
        </ul>
    </div>

    <!-- 재고 현황 -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">재고 현황</h3>
            <button class="text-sm text-blue-500 hover:underline">전체 기간 기준 ↻</button>
        </div>
        <ul class="space-y-2">
            <li class="flex justify-between">
                <span>전체 SKU상품</span>
                <span class="font-bold text-gray-800">4,002</span>
            </li>
            <li class="flex justify-between">
                <span>재고부족</span>
                <span class="font-bold text-red-500">0</span>
            </li>
            <li class="flex justify-between">
                <span>판매불가(품절)</span>
                <span class="font-bold text-gray-800">272</span>
            </li>
        </ul>
    </div>

    <!-- 클레임 현황 -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">클레임 현황</h3>
            <button class="text-sm text-blue-500 hover:underline">최근 1개월 기준 ↻</button>
        </div>
        <ul class="space-y-2">
            <li class="flex justify-between">
                <span>취소요청</span>
                <span class="font-bold text-gray-800">0</span>
            </li>
            <li class="flex justify-between">
                <span>반품요청</span>
                <span class="font-bold text-gray-800">0</span>
            </li>
            <li class="flex justify-between">
                <span>교환요청</span>
                <span class="font-bold text-gray-800">0</span>
            </li>
        </ul>
    </div>

    <!-- 문의 현황 -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">문의 현황(신규)</h3>
            <button class="text-sm text-blue-500 hover:underline">최근 1개월 기준 ↻</button>
        </div>
        <ul class="space-y-2">
            <li class="flex justify-between">
                <span>신규문의</span>
                <span class="font-bold text-gray-800">0</span>
            </li>
            <li class="flex justify-between">
                <span>긴급메시지</span>
                <span class="font-bold text-red-500">0</span>
            </li>
            <li class="flex justify-between">
                <span>상품평</span>
                <span class="font-bold text-gray-800">0</span>
            </li>
        </ul>
    </div>
</div>


<div class="grid grid-cols-2 gap-4 mt-8">
    <!-- 셀플로우 공지사항 -->
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold mb-4">셀플로우 공지사항</h2>
        <ul class="divide-y divide-gray-200">
            @foreach($sellflowNotices as $notice)
                <li class="py-2">
                    <a href="{{ $notice['link'] }}" class="text-blue-500 hover:underline">{{ $notice['title'] }}</a>
                    <p class="text-sm text-gray-500">{{ $notice['date'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- 쇼핑몰 공지사항 -->
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold mb-4">쇼핑몰 공지사항</h2>
        <ul class="divide-y divide-gray-200">
            @foreach($shoppingMallNotices as $notice)
                <li class="py-2">
                    <a href="{{ $notice['link'] }}" class="text-blue-500 hover:underline">{{ $notice['title'] }}</a>
                    <p class="text-sm text-gray-500">{{ $notice['date'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        console.log('대시보드 JavaScript 실행');

        const ctx = document.getElementById('lineChart').getContext('2d');
        if (!ctx) {
            console.error('Canvas element를 찾을 수 없습니다.');
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['월', '화', '수', '목', '금'],
                datasets: [{
                    label: '매출 (₩)',
                    data: [150000, 200000, 175000, 250000, 300000], // 매출 데이터 (원화)
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.2)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return value.toLocaleString('ko-KR') + '₩'; // 숫자를 원화 형식으로 표시
                            }
                        }
                    },
                    x: {}
                }
            }
        });
    });
</script>
@endpush
