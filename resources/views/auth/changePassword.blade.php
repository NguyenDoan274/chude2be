@extends('layouts.main-layout')

@section('content')
<div class="container mt-5">
    <div class="col-md-6 offset-md-3 card p-4 shadow">
        <h4 class="text-center mb-4">🔒 Đổi mật khẩu</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('auth.updatePassword') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>Mật khẩu hiện tại</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Mật khẩu mới</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Xác nhận mật khẩu mới</label>
                <input type="password" name="new_password_confirmation" class="form-control" required>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Cập nhật mật khẩu</button>
                <a href="{{ route('auth.profile') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
