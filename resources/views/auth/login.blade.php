@extends('sinhvien.layout')

@section('content')
<div class="container mt-5">
    <div class="col-md-4 offset-md-4 card p-4 shadow">
        <h4 class="text-center mb-3">Đăng nhập giảng viên</h4>

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            @if ($errors->has('login_error'))
                <div class="text-danger small mb-2 text-center">{{ $errors->first('login_error') }}</div>
            @endif

            <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
        </form>
    </div>
</div>
@endsection
