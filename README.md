
# SellFlow Project

SellFlow는 쇼핑몰(특히 Cafe24)과 연동하여 상품 정보를 수집하고, 사용자별로 데이터베이스를 동적으로 구성하는 Laravel 기반의 통합 관리 시스템입니다.

---

## 📁 프로젝트 구조 요약

```
sellflow_project-master/
├── app/                  # 컨트롤러, 서비스, 모델 등 핵심 비즈니스 로직
├── bootstrap/            # Laravel 부트스트랩 관련 설정
├── config/               # 설정 파일들 (database.php 등)
├── database/             # 마이그레이션 및 시더
├── public/               # 웹 루트, index.php 포함
├── resources/
│   └── views/            # Blade 템플릿 (연동 UI)
├── routes/
│   └── web.php           # 라우터 설정
├── storage/              # 로그, 캐시, 세션 등
├── tests/                # 테스트 코드
├── .env.example          # 환경변수 예시
├── vite.config.js        # Vite 프론트엔드 번들 설정
└── composer.json         # 의존성 관리
```

---

## ✅ 현재까지 구현된 핵심 기능

### 1. OAuth 2.0 인증 (Cafe24 + PKCE 지원)
- Cafe24 쇼핑몰 연동을 위한 Authorization Code Grant + PKCE 흐름 구현
- Access Token / Refresh Token 발급 및 저장
- 메인 DB(`oauth_integrations`) + 유저 DB(`shopping_mall_integrations`) 이중 저장

### 2. 토큰 자동 갱신
- `expires_at` 기준 10분 이내면 자동으로 Refresh 처리
- 실패 시 UI 알림 및 로그 기록

### 3. 사용자별 동적 데이터베이스
- 로그인 사용자 ID 기준으로 `sellflow_global_{id}` DB에 연결
- 각 사용자별 쇼핑몰 연동 정보를 해당 DB에 별도로 저장

### 4. 쇼핑몰 연동 관리 UI
- 연동 목록 출력 (integration.blade.php)
- 연동 추가 / 수정 / 삭제
- Access Token 유효성 검사 및 테스트 버튼 제공
- Refresh Token 남은 시간 표시

---

## 📌 향후 개발 예정 기능

- 쇼핑몰 상품 수집 API 연동 (Cafe24)
- 주문, 반품, 교환 이벤트 웹훅 처리
- 알림 시스템 (SMS, FCM, Email)
- 스마트스토어, 쿠팡 등 타 쇼핑몰 플랫폼 추가 연동
- Stripe 기반 요금제 관리 및 API 호출 제한 설정

---

## ⚙️ 사용 기술 스택

- Laravel 11.x
- MySQL 8.x
- PHP 8.4
- Redis 5.x
- Tailwind CSS + Vite
- GitHub + Composer

---

> 본 프로젝트는 멀티 테넌시 구조를 기반으로 쇼핑몰 운영 데이터를 효율적으로 수집/관리하기 위해 설계되었습니다.


## 🛠️ 개발 이력


✅ 최근 작업 내역 (2025-05-27 기준)

📦 기능 개선: 쇼핑몰 상품 수집 UI
1. 쇼핑몰 유형 선택 카드 UI 개선
기존 select box → 카드 형태(grid layout)로 변경

각 카드에는 로고 이미지와 플랫폼 이름 표시

연동된 쇼핑몰만 표시 (get-shop-types API 기반)

선택 시 배경색, 외곽선, 체크 아이콘으로 시각적 효과 적용됨

2. 선택된 카드에 따라 계정 정보 자동 로드
선택된 카드에 해당하는 쇼핑몰 유형이 hidden input에 자동 반영됨

계정 정보는 AJAX로 /products/get-accounts 통해 로드됨

3. 카드 크기 및 레이아웃 최적화
grid-cols-2 ~ xl:grid-cols-6 반응형 설정

카드가 많아도 너무 커지지 않고, 적어도 정렬 유지됨

4. 수집 폼 슬라이드 토글 기능 추가
"쇼핑몰 상품 수집 열기/접기" 버튼으로 토글 가능

max-height + transition 기반 슬라이딩 효과 적용

상품 목록(products)이 존재할 경우 폼은 기본적으로 접힌 상태로 시작

5. 상품 리스트 렌더링
fetchProducts()로 비동기 로딩

체크박스 선택 시 배경 강조 및 일괄 처리 버튼 작동 (신규/매칭/제외)



✅ 최근 작업 내역 (2025-05-26 기준)

1️⃣ Cafe24 상품 수집 로직 리팩토링
기존 since_product_no 방식 → offset 기반 수집 방식으로 변경

무한 루프 및 중복 수집 문제 해결

limit=100, offset+=100 방식으로 최대 5000개까지 안정적으로 수집

importProducts() 함수 내에서 전체 수집 흐름 일원화

별도 fetchProducts() 함수 제거

컨트롤러에서 직접 offset 루프를 통해 상품 수집 처리

2️⃣ 날짜 필터 기능 적용
수집 대상 기간을 선택할 수 있도록 옵션 적용 (1d, 3d, 7d, 1m, 6m, 1y)

선택된 기간은 created_start_date / created_end_date 파라미터로 Cafe24 API에 전달됨

3️⃣ 수집 상품 개수 사전 체크
getTotalProductCount() 함수 구현

수집 전 전체 상품 개수를 조회하고, 5000개 초과 시 수집 차단 및 에러 메시지 출력

4️⃣ 상품 옵션 요약 처리 (option_summary 필드)
각 상품의 옵션 정보를 "옵션명:옵션값" 형태로 문자열로 변환하여 DB에 저장

예시: "색상:레드, 사이즈:M"

5️⃣ Access Token 만료 자동 갱신 로직 적용
수집 도중 또는 사전 토큰 만료 감지 시 자동으로 Refresh Token을 사용해 재발급

갱신 후 DB에서 최신 Mall 정보를 재조회하여 새로운 Access Token 반영

🔧 기타 개선 사항
AppServiceProvider.php 내 잘못된 URL::forceScheme() 사용 오류 수정 (Illuminate\Support\Facades\URL import)

로깅 및 예외 처리 강화 (Log::info, Log::error 다수 개선)



### ✅ 2025-05-21

- 쇼핑몰 상품 수집 리스트 UI 개선
  - 테이블 자동 너비 확장 및 오른쪽 여백 제거
  - 상품 이미지 셀 가운데 정렬 및 테두리 적용
  - 상품 이미지 hover 시 고정 위치에 확대 미리보기 이미지 출력 (`movePreview`, `hidePreview` 함수 활용)

- 상품 상태 분류 버튼 추가
  - 각 상품 행에 `신규`, `매칭`, `제외` 개별 버튼 추가
  - 상품을 체크박스로 선택 후 일괄 처리 버튼 (`선택 → 신규`, `선택 → 매칭`, `선택 → 제외`) 추가
  - 전체 선택 체크박스 기능 구현

- 시각적 통일성 강화
  - Tailwind CSS를 활용한 버튼 디자인 적용
  - 버튼 컬러 충돌 문제 해결: `제외` 버튼에 `inline style`로 색상 강제 지정하여 검은 글씨 문제 해결


  ### ✅ 2025-05-22

- 수집 방식 변경:
  - ince_product_no 기반 수집 방식 → offset 기반 방식으로 변경하여 무한 루프 및 중복 수집 문제 해결

- 날짜 필터 정확도 개선:
  - created_start_date, created_end_date 파라미터가 offset 방식과 함께 정상 작동되도록 적용

- 루프 안정성 향상:
    - 각 페이지마다 offset을 증가시켜 수집하며, 더 이상 상품이 없을 경우 루프 종료

- 중복 상품 제거:
    - 응답에서 중복된 product_no는 제외 후 저장 처리

- 불필요한 상품 제외:
     - DB에 이미 status가 '제외', '등록완료'인 상품은 필터링

- 향후 개선 예정:
    - 수집 대상 상품 수 사전 확인 기능
    - 5000개 이상 수집 시 '경고창' 노출


