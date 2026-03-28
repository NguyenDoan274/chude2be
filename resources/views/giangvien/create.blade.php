@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h2>Thêm Giảng viên</h2>
<a href="{{ route('giangvien.index') }}" class="btn btn-secondary">Trở về</a>
    <form action="{{ route('giangvien.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Mã GV</label>
            <input type="text" name="ma_gv" value="{{ old('ma_gv') }}" class="form-control" required>
            @error('ma_gv')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label>Họ tên</label>
            <input type="text" name="ho_ten" value="{{ old('ho_ten') }}" class="form-control" required>
            @error('ho_ten')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="password">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
            @error('password')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="vai_tro">Vai trò</label>
            <select name="vai_tro" class="form-control" required>
                <option value="giang_vien" {{ old('vai_tro') == 'giang_vien' ? 'selected' : '' }}>Giảng viên</option>
                <option value="admin" {{ old('vai_tro') == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">💾 Lưu</button>
        
    </form>
</div>
@endsection
