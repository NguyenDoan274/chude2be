@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h3>✏️ Sửa thông tin sinh viên</h3>
    <form action="{{ route('sinhvien.update', $sinhvien->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-2">
            <label>Mã sinh viên</label>
            <input type="text" name="ma_sv" value="{{ old('ma_sv',$sinhvien->ma_sv) }}" class="form-control" required>
            @error('ma_sv')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-2">
            <label>Họ tên</label>
            <input type="text" name="ho_ten" value="{{ $sinhvien->ho_ten }}" class="form-control" required>
        </div>
        <div class="mb-2">
            <label for="">Lớp</label>

            <div class="input-group">

                <span class="input-group-text">D</span>

                <input type="text"
                    name="lop_y"
                    maxlength="2"
                    class="form-control"
                    style="max-width:60px"
                    value="{{ old('lop_y', $lop_y) }}"
                    required>

                <span class="input-group-text">_TH</span>

                <input type="text"
                    name="lop_z"
                    maxlength="2"
                    class="form-control"
                    style="max-width:60px"
                    value="{{ old('lop_z', $lop_z) }}"
                    required>
            </div>
            <!-- input lớp đầy đủ -->
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

            updateValue(); // chạy lần đầu để set giá trị ban đầu
        </script>
        <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email',$sinhvien->email) }}" class="form-control">
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @if($sinhvien->hinh_anh)
        <div class="mb-2">
        <label>Ảnh hiện tại:</label><br>
        <img src="{{ asset($sinhvien->hinh_anh) }}" width="100" height="100" class="rounded mb-2">
        <label>
            <input type="checkbox" name="xoa_anh" value="1">
            Xóa ảnh hiện tại
        </label>
        </div>
        @endif
        <div class="mb-2">
            <label>Ảnh sinh viên</label>
            <input type="file" name="hinh_anh" class="form-control" accept="image/*">
            @error('hinh_anh')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <button class="btn btn-primary mt-2">Cập nhật</button>
        <a href="{{ route('sinhvien.index') }}" class="btn btn-secondary mt-2">Quay lại</a>
    </form>
</div>
@endsection
