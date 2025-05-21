<!DOCTYPE html>
<html lang="kr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/register.js') }}"></script>    </head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-md w-96">
        <div class="flex justify-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-24 h-24">
        </div>
        <h1 class="text-2xl font-bold mb-4 text-center">회원가입</h1>
        <form method="POST" action="/register" onsubmit="validateForm(event)">
        @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium">이름</label>
                <input type="text" id="name" name="name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded">
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium">이메일</label>
                <div class="flex space-x-2">
                    <input type="email" id="email" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded">
                    <button type="button" id="email-verify-btn" onclick="verifyEmail()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">인증하기</button>
                </div>
                <p id="email-status" class="text-sm mt-1"></p>
            </div>

            <div class="mb-4">
                <label for="contact" class="block text-sm font-medium">연락처</label>
                <input type="text" id="contact" name="contact" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded">
            </div>

            <div class="mb-4">
    <label for="password" class="block text-sm font-medium">비밀번호</label>
    <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded" oninput="validatePassword()">
    <p id="password-error" class="text-red-500 text-sm mt-1"></p>
</div>

<div class="mb-4">
    <label for="password_confirmation" class="block text-sm font-medium">비밀번호 확인</label>
    <input type="password" id="password_confirmation" name="password_confirmation" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded" oninput="validatePassword()">
    <p id="password-confirm-error" class="text-red-500 text-sm mt-1"></p>
</div>

<div class="flex justify-center items-center space-x-4 mb-4">
    <label>
        <input type="radio" name="user_type" value="personal" class="hidden peer" onchange="toggleBusinessFields()" checked />
        <div class="w-32 p-2 text-center rounded-lg border border-gray-300 cursor-pointer peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500">
            개인회원
        </div>
    </label>
    <label>
        <input type="radio" name="user_type" value="business" class="hidden peer" onchange="toggleBusinessFields()" />
        <div class="w-32 p-2 text-center rounded-lg border border-gray-300 cursor-pointer peer-checked:bg-green-500 peer-checked:text-white peer-checked:border-green-500">
            사업자회원
        </div>
    </label>
</div>

<div id="businessFields" class="hidden">
    <div class="mb-4">
        <label for="business_name" class="block text-sm font-medium">사업장 이름</label>
        <input type="text" id="business_name" name="business_name" value="{{ old('business_name') ?? '' }}">
    </div>

    <div class="mb-4">
        <label for="business_owner" class="block text-sm font-medium">대표자 이름</label>
        <input type="text" id="business_owner" name="business_owner" value="{{ old('business_owner') ?? '' }}">
    </div>

    <div class="mb-4">
        <label for="business_registration_number" class="block text-sm font-medium">사업자번호</label>
        <input type="text" id="business_registration_number" name="business_registration_number" value="{{ old('business_registration_number') ?? '' }}">
    </div>
</div>

            <div class="mb-4 flex items-center">
                <input type="checkbox" id="alarm_agree" name="alarm_agree" class="mr-2">
                <label for="alarm_agree" class="text-sm">알림 수신 동의 (SMS, 이메일)</label>
            </div>

            <button type="submit" class="w-full px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
        회원가입
    </button>
            </form>
    </div>

    
    <script>
     function toggleBusinessFields() {
    const userType = document.querySelector('input[name="user_type"]:checked').value;
    const businessFields = document.getElementById('businessFields');

    if (userType === 'business') {
        businessFields.classList.remove('hidden');
    } else {
        businessFields.classList.add('hidden');
        
        // 개인회원 선택 시 사업자 필드 초기화
        document.getElementById('business_name').value = '';
        document.getElementById('business_owner').value = '';
        document.getElementById('business_registration_number').value = '';
    }
}


function validatePassword() {
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("password_confirmation").value;
    const passwordError = document.getElementById("password-error");
    const confirmPasswordError = document.getElementById("password-confirm-error");

    passwordError.textContent = password.length < 8 ? "비밀번호는 8자 이상이어야 합니다." : "";
    confirmPasswordError.textContent = password !== confirmPassword ? "비밀번호가 일치하지 않습니다." : "";
}
 

    </script>
</body>
</html>
