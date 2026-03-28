@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h2>Phân công môn: {{ $lichThi->ten_mon }} --- Phòng: {{$lichThi->phong}}</h2>
    <a href="{{ route('lichthi.index') }}" class="btn btn-secondary">Trở lại</a>
    {{-- Hiển thị thông báo --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($lichThi->trang_thai === 'chua_dien_ra')
        {{-- FORM THÊM PHÂN CÔNG --}}
        <form action="{{ route('lichthi.phancong.save', $lichThi->id) }}" method="POST" class="mb-4">
            @csrf
            <input type="hidden" name="lich_thi_id" value="{{ $lichThi->id }}">
            <div class="mb-3">
                <label for="giang_vien_id" class="form-label">Chọn giảng viên</label>
                <select name="giang_vien_id" id="giang_vien_id" class="form-select select2" required>
                    <option value="">-- Chọn giảng viên --</option>
                    @foreach ($giangViens as $gv)
                        <option value="{{ $gv->id }}">{{ $gv->ho_ten }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Lưu phân công</button>
        </form>
    @else
        <p class="text-danger">Không thể phân công giảng viên nữa.</p>
    @endif

    {{-- DANH SÁCH GIẢNG VIÊN ĐÃ PHÂN CÔNG --}}
    <h4>Giảng viên đã được phân công:</h4>
    <ul class="list-group">
        @forelse($lichThi->phanCongGVs as $pc)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ optional($pc->giangVien)->ho_ten ?? 'Chưa có tên' }}
                @if($lichThi->trang_thai === 'chua_dien_ra')
                    <form 
                        action="{{ route('lichthi.phancong.xoa', ['lichthi' => $lichThi->id, 'phancong' => $pc->id]) }}" 
                        method="POST"
                        onsubmit="return confirm('Bạn có chắc muốn hủy phân công này?')"
                        style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                    </form>
                @endif
            </li>
        @empty
            <li class="list-group-item">Chưa có giảng viên nào được phân công.</li>
        @endforelse
    </ul>
</div>
@endsection
