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

.btn-current-page {
  background-color: #1D4ED8 !important;
  color: #fff !important;
  font-weight: bold !important;
  border-color: #1D4ED8 !important;
}
tr.selected-row {
  background-color: #eef2ff !important; /* bg-indigo-50 */
}
.btn-new {
  background-color: #2563eb !important; /* blue-600 */
  color: white !important;
}
.btn-match {
  background-color: #f59e0b !important; /* yellow-500 */
  color: white !important;
}
.btn-exclude {
  background-color: #ef4444 !important; /* red-500 */
  color: white !important;
}

.shop-card {
  @apply cursor-pointer p-4 border rounded-md bg-white shadow hover:shadow-md transition text-center;
}
.shop-card:hover {
    @apply shadow-lg;
}
.shop-card.selected {
    background-color: #dbeafe !important;  /* Tailwind bg-blue-100 */
  border-color: #2563eb !important;      /* Tailwind blue-600 */
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3) !important;
  transition: background-color 0.2s ease-in-out;
}
.shop-card.selected .checkmark {
    display: block !important;
}
</style>



<div class="mb-4">
    <button id="toggleImportForm"
        class="mb-2 px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">
        쇼핑몰 상품 수집 열기/접기
    </button>


    <div id="importFormWrapper"
     class="transition-all duration-500 ease-in-out overflow-hidden"
     style="max-height: 0;">    <div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">쇼핑몰 상품 수집</h2>

        <!-- ✅ Form 시작 -->
<form id="importForm">
    @csrf

    <!-- 쇼핑몰 선택 카드 -->
    @if (!empty($shopTypes))
    <div id="shopTypeCards"
     class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 mb-4">
    @foreach ($shopTypes as $type)
        <div class="shop-card border rounded-md p-4 text-center cursor-pointer relative transition hover:shadow-md"
             data-type="{{ $type }}">
            <img src="/images/logo-{{ strtolower($type) }}.png" alt="{{ $type }}"
                 class="h-12 mx-auto mb-2 object-contain">
            <p class="font-semibold text-sm">{{ $type }}</p>
            <div class="checkmark hidden absolute top-2 right-2 text-blue-500 text-xl">✔</div>
        </div>
    @endforeach
</div>
    @else
    <p class="text-gray-500">연동된 쇼핑몰이 없습니다.</p>
    @endif

    <!-- Hidden input -->
    <input type="hidden" name="shop_type" id="shop_type">

    <!-- 계정/기간 설정 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 font-medium">쇼핑몰 계정</label>
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
<!-- ✅ Form 끝 -->
    </div>
    </div>
    </div>

    <!-- 수집된 상품 리스트 -->
    <div class="bg-white p-4 shadow-md rounded-md">
        <h3 class="text-xl font-bold mb-4">수집된 상품 목록</h3>

        <!-- 상단 일괄 처리 버튼 -->
        <div class="flex justify-between items-center mb-4 sticky top-0 bg-white z-10 p-2 border-b">
    <div class="flex gap-2">
        <button class="btn-new bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">신규 등록</button>
        <button class="btn-match bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">기존 매칭</button>
        <button class="btn-exclude bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">제외 처리</button>
    </div>
    <div id="selectedCount" class="text-sm text-gray-600">0개 선택됨</div>
</div>
        <!-- 상품 리스트 테이블 -->
        <div class="overflow-x-auto">
        <table class="w-full min-w-fit table-auto text-sm border border-gray-200 rounded overflow-hidden">
<thead class="bg-gray-100 text-gray-700">
    <tr>
        <th class="p-2"><input type="checkbox" id="selectAllCheckbox" onchange="toggleAllCheckboxes(this)"></th>
        <th class="p-2">이미지</th>
        <th class="p-2">상품명</th>
        <th class="p-2">옵션</th>
        <th class="p-2">상품코드</th>
        <th class="p-2 text-right">가격</th>
        <th class="p-2">상태</th>
        <th class="p-2">재고</th>
    </tr>
</thead>
<tbody id="productTableBody" class="divide-y divide-gray-100"></tbody>
</table>
        </div>

        <!-- 페이지네이션 -->
        
        <div id="pagination" class="mt-4 flex justify-center space-x-2"></div>

    </div>
</div>

<!-- 이미지 미리보기 박스 -->
<div id="image-preview" style="display:none; position:fixed; top:100px; left:100px; z-index:9999; border:2px solid #ddd; background:#fff; padding:4px; box-shadow:0 0 10px rgba(0,0,0,0.3)">
  <img id="preview-img" src="" style="max-width:300px; max-height:300px;">
</div>


<!-- 수집 결과 모달 -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white p-6 rounded-lg shadow-lg w-80 text-center">
    <h2 id="modalMessage" class="text-lg font-semibold text-gray-800 mb-4">처리 중...</h2>
    <button onclick="closeModal()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
      확인
    </button>
  </div>
</div>



<script>
let products = [];
let currentPage = 1;
const itemsPerPage = 30;





function renderPagination() {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    const totalPages = Math.ceil(products.length / itemsPerPage);
    const maxVisiblePages = 5; // 중앙에 보여줄 최대 페이지 수
    const half = Math.floor(maxVisiblePages / 2);

    let startPage = Math.max(1, currentPage - half);
    let endPage = Math.min(totalPages, currentPage + half);

    

    if (currentPage <= half) {
        endPage = Math.min(totalPages, maxVisiblePages);
    }

    if (currentPage + half > totalPages) {
        startPage = Math.max(1, totalPages - maxVisiblePages + 1);
    }

    // ◀ 이전 버튼
    if (currentPage > 1) {
        const prev = document.createElement('button');
        prev.innerHTML = '◀';
        prev.className = baseBtnClass;
        prev.onclick = () => {
            currentPage--;
            renderTable();
        };
        pagination.appendChild(prev);
    }

    // ... 생략 왼쪽
    if (startPage > 1) {
        const first = document.createElement('button');
        first.innerText = '1';
        first.className = baseBtnClass;
        first.onclick = () => {
            currentPage = 1;
            renderTable();
        };
        pagination.appendChild(first);

        if (startPage > 2) {
            const dots = document.createElement('span');
            dots.innerText = '...';
            dots.className = 'px-2';
            pagination.appendChild(dots);
        }
    }

    // 숫자 페이지 버튼
    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement('button');
        btn.innerText = i;
        btn.className = baseBtnClass + (i === currentPage ? ' btn-current-page' : '');
        btn.onclick = () => {
            currentPage = i;
            renderTable();
        };
        pagination.appendChild(btn);
    }

    // ... 생략 오른쪽
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const dots = document.createElement('span');
            dots.innerText = '...';
            dots.className = 'px-2';
            pagination.appendChild(dots);
        }

        const last = document.createElement('button');
        last.innerText = totalPages;
        last.className = baseBtnClass;
        last.onclick = () => {
            currentPage = totalPages;
            renderTable();
        };
        pagination.appendChild(last);
    }

    // ▶ 다음 버튼
    if (currentPage < totalPages) {
        const next = document.createElement('button');
        next.innerHTML = '▶';
        next.className = baseBtnClass;
        next.onclick = () => {
            currentPage++;
            renderTable();
        };
        pagination.appendChild(next);
    }
}



const baseBtnClass = 'px-3 py-1 border rounded hover:bg-gray-200 transition';


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
document.addEventListener('DOMContentLoaded', () => {
    fetchShopTypes();
    fetchProducts();
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
        row.classList.add('cursor-pointer', 'hover:bg-gray-50');

        row.innerHTML = `
            <td class="p-2 text-center">
                <input type="checkbox" class="productCheckbox" value="${product.id}">
            </td>
            <td class="p-2 text-center">
                <img src="${product.main_image_url}" class="thumbnail w-16 h-16 object-contain"
                     onmousemove="movePreview(event, this)" onmouseout="hidePreview()">
            </td>
            <td class="p-2">${product.product_name}</td>
            <td class="p-2">${product.option_name ?? '옵션없음'}</td>
            <td class="p-2">${product.product_code}</td>
            <td class="p-2 text-right">${formatPrice(product.price)}원</td>
            <td class="p-2">${product.status}</td>
            <td class="p-2">${product.stock}</td>
        `;

        // ✅ 클릭 시 체크박스 ON/OFF + 배경 강조
        row.addEventListener('click', (e) => {
            const checkbox = row.querySelector('.productCheckbox');
            if (!e.target.classList.contains('productCheckbox')) {
                checkbox.checked = !checkbox.checked;
                toggleRowHighlight(row, checkbox.checked);
                updateSelectedCount();
            }
        });

        // ✅ 체크 상태에 따라 배경 유지 (초기 상태)
        const checkbox = row.querySelector('.productCheckbox');
        toggleRowHighlight(row, checkbox.checked);

        tableBody.appendChild(row);
    });

    renderPagination();
    // ✅ 상품 있으면 수집 폼 접기
    const formWrapper = document.getElementById('importFormWrapper');
    if (products.length > 0 && formWrapper) {
        formWrapper.style.maxHeight = '0px';
    }
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

function formatPrice(price) {
    const num = parseFloat(price);
    if (isNaN(num)) return '0';
    return isNaN(num) ? '0' : Math.floor(num).toLocaleString();
}
</script>

<script>
document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault(); // 기본 폼 제출 막기

    const formData = new FormData(this);

    fetch('/products/import', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const message = data.message || '알 수 없는 결과';
        document.getElementById('modalMessage').innerText = message;
        document.getElementById('statusModal').classList.remove('hidden');
    })
    .catch(err => {
        document.getElementById('modalMessage').innerText = '수집 중 오류 발생: ' + err.message;
        document.getElementById('statusModal').classList.remove('hidden');
    });
});

function closeModal() {
    document.getElementById('statusModal').classList.add('hidden');
    // 모달 닫은 후 페이지 새로고침
    window.location.reload();
}
</script>


<script> // 동작 버튼 구성
function getCheckedProductIds() {
    return Array.from(document.querySelectorAll('.productCheckbox:checked'))
                .map(cb => cb.value);
}

function handleBulkAction(actionType) {
    const ids = getCheckedProductIds();
    if (ids.length === 0) return alert("선택된 상품이 없습니다.");

    fetch('/products/bulk-action', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ action: actionType, ids })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        window.location.reload();
    })
    .catch(err => {
        alert("오류 발생: " + err.message);
    });
}

document.querySelector('.btn-new').addEventListener('click', () => handleBulkAction('new'));
document.querySelector('.btn-match').addEventListener('click', () => handleBulkAction('match'));
document.querySelector('.btn-exclude').addEventListener('click', () => handleBulkAction('exclude'));


function updateSelectedCount() {
    const count = document.querySelectorAll('.productCheckbox:checked').length;
    document.getElementById('selectedCount').textContent = `${count}개 선택됨`;
}

function toggleAllCheckboxes(source) {
    document.querySelectorAll('.productCheckbox').forEach(cb => {
        cb.checked = source.checked;
        toggleRowHighlight(cb.closest('tr'), cb.checked);
    });
    updateSelectedCount();
}

function toggleRowHighlight(row, selected) {
    if (selected) {
        row.classList.add('selected-row');
    } else {
        row.classList.remove('selected-row');
    }

}

// 개별 체크박스 체크 시 행 강조 및 카운트 갱신
document.addEventListener('change', (e) => {
    if (e.target.classList.contains('productCheckbox')) {
        toggleRowHighlight(e.target.closest('tr'), e.target.checked);
        updateSelectedCount();
    }
});

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.shop-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.shop-card').forEach(c => {
                c.classList.remove('selected');
                c.querySelector('.checkmark')?.classList.add('hidden');
            });

            card.classList.add('selected'); // ✅ 이 라인이 핵심
            card.querySelector('.checkmark')?.classList.remove('hidden');
            document.getElementById('shop_type').value = card.dataset.type;

            fetchAccounts(); // ✅ 선택 후 계정 갱신
        });
    });
});


</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const formWrapper = document.getElementById('importFormWrapper');
    const toggleBtn = document.getElementById('toggleImportForm');

    if (!formWrapper || !toggleBtn) {
        console.warn('❌ formWrapper or toggleImportForm not found.');
        return;
    }

    let isOpen = true;

    const setWrapperHeight = (expand) => {
        formWrapper.style.maxHeight = expand ? '1000px' : '0px';
    };

    toggleBtn.addEventListener('click', () => {
        isOpen = !isOpen;
        setWrapperHeight(isOpen);
    });

    // ✅ 서버에서 count($products) > 0 이면 접은 상태로 시작
    const hasProducts = {!! count($products ?? []) > 0 ? 'true' : 'false' !!};
    if (hasProducts) {
        isOpen = false;
        setWrapperHeight(false);
    } else {
        setWrapperHeight(true);
    }
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const formWrapper = document.getElementById('importFormWrapper');
        const hasProducts = {{ count($products ?? []) > 0 ? 'true' : 'false' }};
        
        if (formWrapper) {
            formWrapper.style.maxHeight = hasProducts ? '0px' : '1000px';
        }
    });
</script>


@endsection
