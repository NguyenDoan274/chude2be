@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">

    <h3 class="mb-3">✏️ Chỉnh sửa môn học</h3>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('monhoc.update', $monHoc->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Mã môn</label>
                    <input type="text" name="ma_mon" class="form-control"
                           value="{{ old('ma_mon', $monHoc->ma_mon) }}" required>
                    @error('ma_mon')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Tên môn</label>
                    <input type="text" name="ten_mon" class="form-control"
                           value="{{ old('ten_mon', $monHoc->ten_mon) }}" required>
                    @error('ten_mon')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('monhoc.index') }}" class="btn btn-secondary">⬅️ Quay lại</a>
                    <button type="submit" class="btn btn-primary">💾 Lưu thay đổi</button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
