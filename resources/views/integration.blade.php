@extends('layouts.app')

@section('content')
<div class="container">
    <h2>🔗 쇼핑몰 연동</h2>
    <p>쇼핑몰의 Cafe24 ID를 입력하고 연동을 진행하세요.</p>

    <form action="{{ route('app.oauth.redirect') }}" method="GET">
        @csrf
        <div class="form-group">
            <label for="mall_id">쇼핑몰 ID (예: myshop)</label>
            <input type="text" name="mall_id" id="mall_id" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success mt-3">연동하기</button>
    </form>
</div>
@endsection
