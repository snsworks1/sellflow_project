
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
