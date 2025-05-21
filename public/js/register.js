// 이메일 유효성 검사 함수
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // 이메일 유효성 검사 정규식
    return regex.test(email); // 이메일 형식이 유효하면 true 반환
}

// 이메일 인증 요청 함수
window.verifyEmail = function() {
    console.log('verifyEmail 함수 호출됨');
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMeta) {
        console.error('CSRF 토큰을 찾을 수 없습니다.');
        return;
    }

    const emailInput = document.getElementById("email");
    const emailStatus = document.getElementById("email-status");
    const verifyButton = document.getElementById("email-verify-btn");
    const email = emailInput.value;

    // 이메일 형식 검사
    if (!validateEmail(email)) {
        emailStatus.style.color = "red";
        emailStatus.textContent = "올바른 이메일 주소를 입력해주세요.";
        return;
    }

    // 버튼 비활성화
    verifyButton.disabled = true;
    emailStatus.style.color = "blue";
    emailStatus.textContent = "인증 이메일을 발송 중입니다...";

    // 인증 요청
    fetch('/verify-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfTokenMeta.content,
        },
        body: JSON.stringify({ email }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            emailStatus.style.color = "green";
            emailStatus.textContent = "인증 이메일이 발송되었습니다.";
            emailInput.setAttribute("data-verified", "false"); // 아직 인증되지 않은 상태

            // 인증 상태를 주기적으로 확인
            checkVerificationStatus(email);
        } else {
            emailStatus.style.color = "red";
            emailStatus.textContent = data.message || "인증 요청에 실패했습니다.";
        }
    })
    .catch(() => {
        emailStatus.style.color = "red";
        emailStatus.textContent = "오류가 발생했습니다. 나중에 다시 시도해주세요.";
    })
    .finally(() => {
        verifyButton.disabled = false; // 버튼 다시 활성화
    });
};

// 이메일 인증 상태 확인
function checkVerificationStatus(email) {
    const intervalId = setInterval(() => {
        fetch(`/email-verification-status?email=${encodeURIComponent(email)}`)
            .then(response => response.json())
            .then(data => {
                if (data.verified) {
                    const emailStatus = document.getElementById("email-status");
                    emailStatus.style.color = "green";
                    emailStatus.textContent = "인증이 완료되었습니다.";
                    const emailInput = document.getElementById("email");
                    emailInput.setAttribute("data-verified", "true");

                    clearInterval(intervalId); // 상태 확인 종료
                }
            })
            .catch(() => {
                console.error("인증 상태를 확인할 수 없습니다.");
            });
    }, 3000); // 3초마다 확인
}

// 회원가입 폼 유효성 검사
function validateForm(event) {
    console.log("validateForm 함수 호출됨");

    // 이메일 인증 상태 확인
    const emailInput = document.getElementById("email");
    const emailStatus = document.getElementById("email-status");
    const isVerified = emailInput.getAttribute("data-verified") === "true";

    if (!isVerified) {
        event.preventDefault(); // 폼 제출 방지
        console.log("폼 제출 차단: 이메일 인증 미완료");
        emailStatus.style.color = "red";
        emailStatus.textContent = "이메일 인증을 완료해주세요.";
        return; // 이메일 인증 미완료 시 종료
    }

    // 유효하지 않으면 폼 제출 방지
    if (!isValid) {
        event.preventDefault(); // 폼 제출 방지
        console.log("폼 제출 차단: 비밀번호 조건 미충족");
    } else {
        console.log("폼 제출 허용: 이메일 인증 및 비밀번호 조건 충족");
    }
    console.log("폼 제출 준비 완료");
    return true; // 폼 제출을 허용합니다.
}


// 페이지 로드 시 이메일 인증 상태 확인
document.addEventListener("DOMContentLoaded", function () {
    const emailInput = document.getElementById("email");
    const emailStatus = document.getElementById("email-status");

    if (!emailInput.value) return; // 이메일 입력 필드가 비어있으면 종료

    // 서버에서 인증 상태 확인
    fetch(`/email-verification-status?email=${encodeURIComponent(emailInput.value)}`)
        .then(response => response.json())
        .then(data => {
            if (data.verified) {
                emailStatus.style.color = "green";
                emailStatus.textContent = "인증이 완료되었습니다.";
                emailInput.setAttribute("data-verified", "true");
            } else {
                emailStatus.style.color = "red";
                emailStatus.textContent = "이메일 인증이 필요합니다.";
                emailInput.setAttribute("data-verified", "false");
            }
        })
        .catch(() => {
            emailStatus.style.color = "red";
            emailStatus.textContent = "인증 상태를 확인할 수 없습니다.";
        });
});



// 디버깅용 로그 출력
console.log('register.js 로드 완료');
