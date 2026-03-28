@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h2>Sửa Giảng viên</h2>

    <form action="{{ route('giangvien.update', $giangvien->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Mã GV</label>
            <input type="text" name="ma_gv" value="{{ $giangvien->ma_gv }}" class="form-control" required>
            @error('ma_gv')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label>Họ tên</label>
            <input type="text" name="ho_ten" value="{{ $giangvien->ho_ten }}" class="form-control" required>
            @error('ho_ten')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" value="{{ $giangvien->email }}" class="form-control">
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="{{ route('giangvien.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection
