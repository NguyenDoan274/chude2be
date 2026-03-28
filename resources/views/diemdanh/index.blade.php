@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">📅 Danh sách phòng thi</h2>

    {{-- Thanh tìm kiếm --}}
    <form method="GET" action="{{ route('diemdanh.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-control" placeholder="🔍 Tìm theo phòng hoặc tên môn...">
            <button class="btn btn-primary" type="submit">Tìm kiếm</button>
            @if(request('search'))
                <a href="{{ route('diemdanh.index') }}" class="btn btn-outline-secondary">Xóa lọc</a>
            @endif
        </div>
    </form>

    {{-- Bảng danh sách --}}
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Tên môn</th>
                <th>Ngày thi</th>
                <th>Phòng</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lichThis as $lich)
                <tr>
                    <td>{{ $loop->iteration + ($lichThis->currentPage() - 1) * $lichThis->perPage() }}</td>
                    <td>{{ $lich->monHoc->ten_mon  }}</td>
                    <td>{{ $lich->thoi_gian_thi->format('d/m/Y H:i') }}</td>
                    <td>{{ $lich->phong }}</td>
                    <td>
                        @if($lich->trang_thai === 'da_ket_thuc')
                            <span class="badge bg-secondary">Đã kết thúc</span>
                        @elseif($lich->trang_thai === 'dang_dien_ra')
                            <span class="badge bg-success">Đang diễn ra</span>
                        @elseif($lich->trang_thai === 'chua_dien_ra')
                            <span class="badge bg-warning text-dark">Chưa diễn ra</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('diemdanh.show', $lich->id) }}" class="btn btn-primary btn-sm">
                            👁️ Xem sinh viên
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Không có lịch thi nào</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Phân trang --}}
    <div class="d-flex justify-content-center">
        {{ $lichThis->appends(['search' => request('search')])->links() }}
    </div>
</div>
@endsection
