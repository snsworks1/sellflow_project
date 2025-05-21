<!DOCTYPE html>
<html>
<head>
    <title>이메일 인증</title>
</head>
<body>
    <h1>이메일 인증을 완료해주세요</h1>
    <p>아래 버튼을 클릭하여 이메일 인증을 완료하세요:</p>
    <a href="{{ $url }}" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">이메일 인증하기</a>
    <p>혹시 버튼이 작동하지 않으면 다음 링크를 브라우저에 복사하여 접속하세요:</p>
    <p>{{ $url }}</p>
</body>
</html>