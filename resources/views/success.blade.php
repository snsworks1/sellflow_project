<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SellFlow 앱 설치 완료</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white shadow-lg rounded-lg p-8 max-w-lg text-center">
        <img src="{{ asset('images/logo.png') }}" alt="SellFlow 로고" class="w-24 mx-auto mb-4">
        
        <h2 class="text-2xl font-semibold text-gray-800">SellFlow 앱 설치 완료 🎉</h2>
        <p class="text-gray-600 mt-2">
            CAFE24 SellFlow 앱이 성공적으로 설치되었습니다. <br>
            이제 쇼핑몰 설정에서 CAFE24 연동을 진행해주세요.
        </p>

        <div class="mt-6">
            <a href="{{ route('integration.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium px-6 py-2 rounded transition duration-200">
                쇼핑몰 연동하러 가기
            </a>
        </div>

        <div class="mt-4 text-gray-500 text-sm">
            문제가 발생하면 <a href="mailto:support@sellflow.kr" class="text-blue-500 underline">고객센터</a>로 문의해주세요.
        </div>
    </div>

</body>
</html>
