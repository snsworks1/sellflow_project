<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BusinessInfo; // 추가
use Illuminate\Support\Facades\Hash;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan; // 마이그레이션 실행에 사용



class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register'); // 회원가입 페이지 표시
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            // 1. 유효성 검사
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:8',
                'user_type' => 'required|in:personal,business',
                'alarm_agreement' => 'nullable|boolean',
                
                 // 사업자회원 필드는 user_type이 'business'일 때만 유효성 검사를 진행
                'business_name' => 'nullable|required_if:user_type,business|string|max:255',
                'business_owner' => 'nullable|required_if:user_type,business|string|max:255',
                'business_registration_number' => 'nullable|required_if:user_type,business|string|max:20',
            ]);
    
            // 2. 메인 데이터베이스에 회원 정보 저장
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'account_type' => $validated['user_type'],
                'alarm_agreement' => $validated['alarm_agreement'] ?? false,
            ]);
    
            // 3. 사업자 회원 추가 정보 저장
            if ($validated['user_type'] === 'business') {
                BusinessInfo::create([
                    'user_id' => $user->id,
                    'business_name' => $validated['business_name'],
                    'owner_name' => $validated['owner_name'], // 수정된 필드 이름
                    'registration_number' => $validated['registration_number'], // 수정된 필드 이름
                ]);
            }
    
            // 4. 계정별 데이터베이스 생성
            $databaseName = "sellflow_global_" . $user->id;
    
            DB::statement("CREATE DATABASE `$databaseName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
            // 5. 동적 연결 설정 및 마이그레이션 실행
            config(['database.connections.dynamic.database' => $databaseName]);
            Artisan::call('migrate', [
                '--path' => 'database/migrations/user',
                '--database' => 'dynamic',
            ]);
    
            // 6. 기본 연결 복원
            DB::setDefaultConnection('main');
    
            DB::commit();
    
            return redirect()->route('login')->with('success', '회원가입이 완료되었습니다.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('회원가입 중 에러 발생: ' . $e->getMessage());
            return back()->withErrors(['error' => '회원가입 중 문제가 발생했습니다.']);
        }
    }
    

}
