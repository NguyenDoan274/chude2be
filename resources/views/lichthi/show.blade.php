@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h3>🧾 Danh sách sinh viên phòng {{ $lichThi->phong }} – {{ $lichThi->monHoc->ten_mon }}</h3>

    <a href="{{ route('lichthi.index') }}" class="btn btn-secondary mb-3">Trở lại</a>

    {{-- Thanh tìm kiếm --}}
    <form method="GET" action="{{ route('lichthi.show', $lichThi->id) }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ request('search') }}" 
                   class="form-control" placeholder="🔍 Tìm theo MSSV hoặc tên...">
            <button class="btn btn-primary" type="submit">Tìm kiếm</button>
            @if(request('search'))
                <a href="{{ route('lichthi.show', $lichThi->id) }}" class="btn btn-outline-secondary">Xóa lọc</a>
            @endif
        </div>
    </form>

    <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>STT</th>
                    <th>MSSV</th>
                    <th>Họ tên</th>
                    <th>Lớp</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sinhViensFiltered = $sinhViens;
                    if(request('search')) {
                        $search = strtolower(request('search'));
                        $sinhViensFiltered = $sinhViens->filter(function($sv) use ($search) {
                            return str_contains(strtolower($sv->sinhVien->ma_sv), $search)
                                || str_contains(strtolower($sv->sinhVien->ho_ten), $search)
                                || str_contains(strtolower($sv->sinhVien->lop), $search);
                        });
                    }
                @endphp

                @forelse($sinhViensFiltered as $sv)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $sv->sinhVien->ma_sv }}</td>
                        <td>{{ $sv->sinhVien->ho_ten }}</td>
                        <td>{{ $sv->sinhVien->lop }}</td>
                        <td>
                            @if(Auth::user()->vai_tro === 'admin' && $lichThi->trang_thai === 'chua_dien_ra')
                            <form action="{{ route('lichthi.removeStudent', $sv->id) }}" 
                                method="POST" 
                                onsubmit="return confirm('Xóa sinh viên {{ $sv->sinhVien->ho_ten }} khỏi ca thi này?');">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            Không tìm thấy sinh viên phù hợp.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
</div>
@endsection