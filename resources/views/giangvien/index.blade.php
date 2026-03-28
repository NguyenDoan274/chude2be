@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h2>Danh sách Giảng viên</h2>
    @if(Auth::user()->vai_tro === 'admin')
    <div class="mb-3 text-end">
    <a href="{{ route('giangvien.create') }}" class="btn btn-success mb-3">+ Thêm Giảng viên</a>
    </div> 
    @endif
    {{-- Thanh tìm kiếm --}}
    <form method="GET" action="{{ route('giangvien.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-control" placeholder="🔍 Tìm theo họ tên giảng viên...">
            <button class="btn btn-primary" type="submit">Tìm kiếm</button>
            @if(request('search'))
                <a href="{{ route('giangvien.index') }}" class="btn btn-outline-secondary">Xóa lọc</a>
            @endif
        </div>
    </form>
    <br>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Mã GV</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @foreach($giangviens as $gv)
                <tr>
                    <td>{{ $gv->ma_gv }}</td>
                    <td>{{ $gv->ho_ten }}</td>
                    <td>{{ $gv->email }}</td>
                   
                    <td>
                        <a href="{{ route('giangvien.edit', $gv->id) }}" class="btn btn-primary btn-sm">Sửa</a>
                        <form action="{{ route('giangvien.destroy', $gv->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Xóa giảng viên này?')">Xóa</button>
                        </form>
                        <a href="{{ route('giangvien.phancong', $gv->id) }}" class="btn btn-info btn-sm">Phân công</a>
                    </td>
                    
                </tr>
            @endforeach
        </tbody>
        <br>
        @if(Auth::user()->vai_tro === 'admin')
        <form action="{{ route('giangvien.import') }}" method="POST" enctype="multipart/form-data" class="mb-3">
    @csrf
    <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
    <button type="submit" class="btn btn-success">Import Excel</button>
</form>
@endif

    </table>
</div>
@endsection
