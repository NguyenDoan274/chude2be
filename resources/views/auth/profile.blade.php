@extends('layouts.main-layout')

@section('content')
<div class="container mt-5">
    <div class="col-md-6 offset-md-3 card p-4 shadow">
        <h4 class="text-center mb-4">Thông tin tài khoản</h4>
        
        <table class="table table-borderless">
            <tr>
                <th>Họ và tên:</th>
                <td>{{ $user->ho_ten }}</td>
            </tr>
            <tr>
                <th>Email:</th>
                <td>{{ $user->email }}</td>
            </tr>
        </table>

        <div class="text-center mt-4">
            <a href="{{ route('auth.changePassword') }}" class="btn btn-primary">Đổi mật khẩu</a>
        </div>
    </div>
</div>
@endsection
