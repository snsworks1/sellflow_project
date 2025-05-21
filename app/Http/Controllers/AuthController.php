<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BusinessInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\Models\EmailVerification; //하단 3개 이메일 인증
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;




class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'user_type' => 'required|in:personal,business',
            'contact' => 'required|string|max:20',
            'business_name' => 'nullable|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'business_number' => 'nullable|string|max:20',
            'alarm_agree' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'contact' => $request->contact,
            'alarm_agree' => $request->alarm_agree ?? false,
        ]);

        if ($request->user_type === 'business') {
            BusinessInfo::create([
                'user_id' => $user->id,
                'business_name' => $request->business_name,
                'owner_name' => $request->owner_name,
                'business_number' => $request->business_number,
            ]);
        }

        return redirect()->route('login')->with('success', '회원가입이 완료되었습니다. 로그인하세요.');
    }

    public function showRegisterForm()
{
    return view('auth.register'); // 회원가입 Blade 파일로 이동
}

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:8',
    ]);

    $user = User::where('email', $credentials['email'])->first();

    if ($user && Hash::check($credentials['password'], $user->password)) {
        Auth::login($user);
        return redirect()->route('dashboard')->with('success', '로그인 성공');
    }

    return back()->withErrors([
        'email' => '이메일 또는 비밀번호가 올바르지 않습니다.',
    ]);
}

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success', '로그아웃 되었습니다.');
    }

    public function showPasswordRequestForm()
{
    return view('auth.password_request'); // 비밀번호 찾기 페이지
}

public function sendVerificationEmail(Request $request)
{
    $request->validate(['email' => 'required|email']);

    // 기존 인증 상태 초기화
    EmailVerification::where('email', $request->email)->update([
        'token' => null,
        'verified' => false,
    ]);

    // 새로운 인증 토큰 생성
    $token = Str::random(64);

    // 데이터베이스에 저장 또는 업데이트
    EmailVerification::updateOrCreate(
        ['email' => $request->email],
        [
            'token' => $token,
            'verified' => false,
            'created_at' => now(),
        ]
    );

    // 인증 이메일 발송
    $verificationUrl = url('/verify-email/confirm?token=' . $token);
    Mail::to($request->email)->send(new \App\Mail\VerifyEmail($verificationUrl));

    return response()->json(['success' => true, 'message' => '인증 이메일이 발송되었습니다.']);
}


public function confirmVerification(Request $request)
{
    $token = $request->query('token');
    $verification = EmailVerification::where('token', $token)->first();

    if (!$verification) {
        return response()->json(['success' => false, 'message' => '유효하지 않은 인증 토큰입니다.'], 400);
    }

    // 토큰 유효시간 확인 (예: 5분 제한)
    $expiration = $verification->created_at->addMinutes(5);
    if (now()->greaterThan($expiration)) {
        return response()->json(['success' => false, 'message' => '인증 토큰이 만료되었습니다.'], 400);
    }

    // 인증 상태 업데이트
    $verification->update([
        'verified' => true,
        'token' => null, // NULL로 설정
    ]);

    return response()->json(['success' => true, 'message' => '이메일 인증이 완료되었습니다.']);
}



public function getVerificationStatus(Request $request)
{
    $email = $request->query('email');

    // 해당 이메일의 인증 상태 확인
    $verification = EmailVerification::where('email', $email)->first();

    if ($verification && $verification->verified) {
        return response()->json(['verified' => true]);
    }

    return response()->json(['verified' => false]);
}

public function invalidateVerification(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $verification = EmailVerification::where('email', $request->email)->first();

    if ($verification) {
        // 인증 데이터 삭제 또는 초기화
        $verification->update([
            'token' => null,        // 토큰 삭제
            'verified' => false,   // 인증 상태 초기화
        ]);
    }

    return response()->json(['success' => true, 'message' => '인증 상태가 초기화되었습니다.']);
}

}