@extends('layouts.main-layout')
@section('search')
   
@endsection
@section('content')
<div class="container mt-4">
    <h3>➕ Thêm sinh viên</h3>
    <a href="{{ route('sinhvien.index') }}" class="btn btn-secondary mt-2">Trở về</a>
    <form action="{{ route('sinhvien.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-2">
            <label>Mã sinh viên</label>
            <input type="text" name="ma_sv" value="{{ old('ma_sv') }}" class="form-control" required>
            @error('ma_sv')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-2">
            <label>Họ tên</label>
            <input type="text" name="ho_ten" value="{{ old('ho_ten') }}" class="form-control" required>
        </div>
        <div class="mb-2">
            <label for="">Lớp</label>

            <div class="input-group">
                <span class="input-group-text">D</span>
                
                <input type="text" 
                    name="lop_y" 
                    minlength="2"
                    maxlength="2"
                    class="form-control"
                    style="max-width:60px"
                    value="{{ old('lop_y', substr(now()->year, -2)) }}"
                    pattern="\d{2}"
                    title="Vui lòng nhập đúng 2 số"
                    required>
                
                <span class="input-group-text">_TH</span>
                
                <input type="text" 
                    name="lop_z" 
                    minlength="2"
                    maxlength="2"
                    class="form-control"
                    style="max-width:60px"
                    value="{{ old('lop_z') }}"
                    pattern="\d{2}"
                    title="Vui lòng nhập đúng 2 số"
                    required>
            </div>

            <!-- input tổng gom lại -->
            <input type="hidden" name="lop" id="lop_full">
        </div>

        <script>
            const inputY = document.querySelector("input[name='lop_y']");
            const inputZ = document.querySelector("input[name='lop_z']");
            const full = document.getElementById("lop_full");

            function updateValue() {
                full.value = `D${inputY.value}_TH${inputZ.value}`;
            }

            inputY.addEventListener("input", updateValue);
            inputZ.addEventListener("input", updateValue);
            updateValue();
        </script>

        <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-2">
            <label>Ảnh sinh viên</label>
            <input type="file" name="hinh_anh" class="form-control" accept="image/*">
            @error('hinh_anh')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <button class="btn btn-success mt-2">Lưu</button>
        
    </form>
</div>
@endsection
