@extends('layouts.app')

@section('content')

<style>
#image-preview {
  all: initial; /* 다른 스타일 초기화 */
  display: none;
  position: fixed;
  z-index: 9999;
  border: 2px solid #ddd;
  background: white;
  padding: 4px;
  box-shadow: 0 0 10px rgba(0,0,0,0.3);
}

#image-preview img {
  all: initial;
  max-width: 300px;
  max-height: 300px;
}

</style>

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">쇼핑몰 상품 수집</h2>

    <!-- 수집 설정 폼 (간소화) -->
    <div class="bg-white p-4 shadow-md rounded-md mb-4">
        <form id="importForm" method="POST" action="/products/import">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium">쇼핑몰 타입</label>
                    <select id="shop_type" name="shop_type" class="w-full p-2 border rounded-md">
                        <option value="Cafe24">Cafe24</option>
                        <option value="SmartStore">SmartStore</option>
                        <option value="Coupang">Coupang</option>
                        <option value="ESMPlus">ESMPlus</option>
                    </select>

                    <label for="shop_account" class="block text-lg font-medium text-gray-700 mb-2">쇼핑몰 계정:</label>
                    <select id="shop_account" name="shop_account" class="w-full p-3 border rounded-md mb-4">
    <option value="">먼저 쇼핑몰 유형을 선택해주세요</option>
</select>
                </div>
                
                <div>
                    <label class="block mb-1 font-medium">수집 기간</label>
                    <select name="date_range" class="w-full p-2 border rounded-md">
                        <option value="1d">1일</option>
                        <option value="3d">3일</option>
                        <option value="7d">7일</option>
                        <option value="1m">1개월</option>
                        <option value="6m">6개월</option>
                        <option value="1y">1년</option>
                        <option value="all">전체</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">상품 수집 시작</button>
            </div>
        </form>
    </div>

    <!-- 수집된 상품 리스트 -->
    <div class="bg-white p-4 shadow-md rounded-md">
        <h3 class="text-xl font-bold mb-4">수집된 상품 목록</h3>

        <div class="mb-2 flex flex-wrap gap-2">
  <button onclick="bulkMark('new')" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg shadow-md transition">선택 → 신규</button>
  <button onclick="bulkMark('match')" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow-md transition">선택 → 매칭</button>
  <button 
  onclick="bulkMark('exclude')" 
  style="background-color:#334155 !important; color:#fff !important;" 
  class="px-4 py-2 font-semibold rounded-lg shadow-md transition">
  선택 → 제외
</button>
</div>
        <!-- 상품 리스트 테이블 -->
        <div class="overflow-x-auto">
        <table class="w-full min-w-fit table-auto bg-white border border-gray-200">
        <thead>
                    <tr class="bg-gray-100">
                    <th class="p-2 border">
                    <input type="checkbox"
       id="selectAllCheckbox"
       onchange="toggleAllCheckboxes(this)"
       class="productCheckbox accent-indigo-500 w-5 h-5 rounded border-gray-300 shadow-sm hover:ring-2 hover:ring-indigo-300">

                    </th>
                        <th class="image-column">이미지</th>
                        <th class="p-2 border">상품명</th>
                        <th class="p-2 border">상품코드</th>
                        <th class="p-2 border">가격</th>
                        <th class="p-2 border">상태</th>
                        <th class="p-2 border">재고</th>
                        <th class="p-2 border">동작</th>
                    </tr>
                </thead>
                <tbody id="productTableBody"></tbody>
            </table>
        </div>

        <!-- 페이지네이션 -->
        <div class="mt-4 flex justify-center space-x-2">
            <button id="prevPage" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">이전</button>
            <span id="currentPage" class="px-3 py-1">1</span>
            <button id="nextPage" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">다음</button>
        </div>
    </div>
</div>

<!-- 이미지 미리보기 박스 -->
<div id="image-preview" style="display:none; position:fixed; top:100px; left:100px; z-index:9999; border:2px solid #ddd; background:#fff; padding:4px; box-shadow:0 0 10px rgba(0,0,0,0.3)">
  <img id="preview-img" src="" style="max-width:300px; max-height:300px;">
</div>

<script>
let products = [];
let currentPage = 1;
const itemsPerPage = 30;

document.addEventListener('DOMContentLoaded', () => {
    fetchProducts();
});

// 쇼핑몰 유형을 서버에서 가져와서 드롭다운에 표시
function fetchShopTypes() {
    console.log('🚀 fetchShopTypes() 실행됨'); // ✅ 이게 콘솔에 찍히는지 확인

    fetch('/products/get-shop-types')
        .then(response => response.json())
        .then(data => {
            console.log('✅ 가져온 쇼핑몰 유형:', data.shop_types);

            const shopTypeSelect = document.getElementById('shop_type');
            if (!shopTypeSelect) {
                console.error('❌ shop_type 요소를 찾을 수 없습니다.');
                return;
            }

            shopTypeSelect.innerHTML = '<option value="">쇼핑몰을 선택해주세요</option>';
            
            data.shop_types.forEach(type => {
                shopTypeSelect.innerHTML += `<option value="${type}">${type}</option>`;
            });

            console.log('✅ shop_type 옵션 업데이트 완료');
        })
        .catch(error => {
            console.error('❌ 쇼핑몰 유형 가져오기 중 오류:', error);
        });
}

// 선택한 쇼핑몰 유형에 해당하는 계정 목록을 가져와서 표시
function fetchAccounts() {
    const shopType = document.getElementById('shop_type').value;
    console.log('🟢 선택한 쇼핑몰 유형:', shopType);

    if (!shopType) {
        document.getElementById('shop_account').innerHTML = '<option value="">먼저 쇼핑몰 유형을 선택해주세요</option>';
        return;
    }

    fetch(`/products/get-accounts?shop_type=${shopType}`)
        .then(response => response.json())
        .then(data => {
            console.log('✅ 서버에서 받아온 계정 목록:', data.accounts);

            const accountSelect = document.getElementById('shop_account');

            if (!accountSelect) {
                console.error('❌ shop_account 요소를 찾을 수 없습니다.');
                return;
            }

            // 기존 옵션 초기화
            accountSelect.innerHTML = '<option value="">쇼핑몰 계정을 선택해주세요</option>';

            // 계정 목록을 select 박스에 추가
            data.accounts.forEach(account => {
                let option = document.createElement('option');
                option.value = account;
                option.textContent = account;
                accountSelect.appendChild(option);
            });

            console.log('✅ shop_account 옵션 업데이트 완료');
        })
        .catch(error => {
            console.error('❌ 계정 가져오기 중 오류:', error);
        });
}



// 페이지 로드 시 자동 호출
document.addEventListener('DOMContentLoaded', function() {
    fetchShopTypes();
    document.getElementById('shop_type').addEventListener('change', fetchAccounts);
});

function fetchProducts() {
    fetch('/api/products/get-products', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('상품 데이터를 가져오는 중 오류가 발생했습니다.');
        }
        return response.json();
    })
    .then(data => {
        console.log('상품 데이터:', data);
        products = data.products || [];
        renderTable();
    })
    .catch(error => {
        console.error('상품 가져오기 중 오류:', error);
    });
}

function renderTable() {
    const tableBody = document.getElementById('productTableBody');
    tableBody.innerHTML = '';

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageItems = products.slice(startIndex, endIndex);

    pageItems.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="p-2 border">
  <input type="checkbox"
         class="productCheckbox accent-indigo-500 w-5 h-5 rounded border-gray-300 shadow-sm hover:ring-2 hover:ring-indigo-300">
</td>

           <td class="image-column border p-2 text-center align-middle">
  <div class="thumbnail-wrapper inline-block">
    <img src="${product.main_image_url}" class="thumbnail" alt="상품 이미지"
     onmousemove="movePreview(event, this)" onmouseout="hidePreview()" style="max-width:80px; max-height:80px;">
  </div>
</td>
            <td class="p-2 border">${product.product_name}</td>
            <td class="p-2 border">${product.product_code}</td>
            <td class="p-2 border">${product.price}원</td>
            <td class="p-2 border">${product.status}</td>
            <td class="p-2 border">${product.stock}</td>
           <td class="p-2 border">
  <div class="flex flex-wrap gap-1">
    <button class="min-w-[64px] px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg shadow-md transition-all">신규</button>
    <button class="min-w-[64px] px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow-md transition-all">매칭</button>
<button style="background-color:#334155 !important; color:#fff !important;"
  class="min-w-[64px] px-3 py-1.5 font-semibold rounded-md shadow-sm transition-all duration-150">
  제외
</button>
  </div>
</td>

        `;
        tableBody.appendChild(row);
    });

    document.getElementById('currentPage').innerText = currentPage;
}



document.addEventListener('DOMContentLoaded', function() {
    fetchShopTypes();
    document.getElementById('shop_type').addEventListener('change', fetchAccounts);

    // ✅ shop_account 변경 시 선택된 값 콘솔 출력
    document.getElementById('shop_account').addEventListener('change', function() {
        console.log('🟢 선택한 쇼핑몰 계정:', this.value);
    });
});
</script>
<script>
function movePreview(event, img) {
  const preview = document.getElementById('image-preview');
  const previewImg = document.getElementById('preview-img');

  previewImg.src = img.src;
  preview.style.display = 'block';

  // 마우스 위치 기준으로 약간 오른쪽/아래로 띄움
  preview.style.top = (event.clientY + 20) + 'px';
  preview.style.left = (event.clientX + 20) + 'px';
}

function hidePreview() {
  document.getElementById('image-preview').style.display = 'none';
}

function toggleAllCheckboxes(source) {
  document.querySelectorAll('.productCheckbox').forEach(checkbox => {
    checkbox.checked = source.checked;
  });
}
</script>

@endsection
