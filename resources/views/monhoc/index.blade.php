@extends('layouts.main-layout') 

@section('content')
<div class="container mt-4">
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>📚 Danh sách môn học</h3>
        <a href="{{ route('monhoc.create') }}" class="btn btn-primary">
            ➕ Thêm môn học
        </a>
    </div>
    {{-- Thanh tìm kiếm --}}
    <form method="GET" action="{{ route('monhoc.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-control" placeholder="🔍 Tìm theo mã môn hoặc tên môn...">
            <button class="btn btn-primary" type="submit">Tìm kiếm</button>
            @if(request('search'))
                <a href="{{ route('monhoc.index') }}" class="btn btn-outline-secondary">Xóa lọc</a>
            @endif
        </div>
    </form>
    <div class="card shadow-sm">
        <div class="card-body p-0">

            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Mã môn</th>
                        <th>Tên môn</th>
                        <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($monHocs as $mon)
                        <tr class="text-center">
                            <td>{{ $mon->ma_mon }}</td>
                            <td class="text-start">{{ $mon->ten_mon }}</td>
                            <td>
                                <a href="{{ route('monhoc.edit', $mon->id) }}" class="btn btn-sm btn-warning">
                                    ✏️ Sửa
                                </a>

                                <form action="{{ route('monhoc.destroy', $mon->id) }}"
                                    method="POST"
                                    style="display:inline-block;"
                                    onsubmit="return confirmDelete();">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        🗑️ Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center p-3">
                                Chưa có môn học nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>
</div>
<script>
function confirmDelete() {
    return confirm('Bạn chắc chắn muốn xóa môn học này?');
}
</script>
@endsection
