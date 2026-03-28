@extends('layouts.main-layout')
@section('content')
<div class="container p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📚 Danh sách sinh viên</h2>
        
        @if(Auth::guard('giangvien')->check() && Auth::guard('giangvien')->user()->vai_tro === 'admin')
        <div class="d-flex gap-2">
            <a href="{{ route('sinhvien.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Thêm sinh viên
            </a>
        </div>
        @endif
    </div>

    @if(request('search'))
        <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
            <span>Kết quả tìm kiếm: "{{ request('search') }}"</span>
            <a href="{{ route('sinhvien.index') }}" class="btn btn-sm btn-outline-secondary">Xóa lọc</a>
        </div>
    @endif

    @if(Auth::guard('giangvien')->check() && Auth::guard('giangvien')->user()->vai_tro === 'admin')
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Import sinh viên từ Excel</h5>
            <form action="{{ route('sinhvien.import') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control form-control-sm w-auto" required>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-file-import"></i> Import Excel
                </button>
            </form>
            @if(session('import_failures'))
            <div class="alert alert-warning">
                <h6 class="mb-2">⚠️ Các dòng import bị lỗi:</h6>
                <ul class="mb-0">
                    @foreach(session('import_failures') as $failure)
                        <li>
                            <strong>Dòng {{ $failure->row() }}:</strong>
                            {{ implode(', ', $failure->errors()) }}
                            <br>
                            <small class="text-muted">
                                Dữ liệu: {{ json_encode($failure->values(), JSON_UNESCAPED_UNICODE) }}
                            </small>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Desktop Table -->
    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Mã SV</th>
                        <th>Họ tên</th>
                        <th>Lớp</th>
                        <th>Email</th>
                        <th>Hình ảnh</th>
                        @if(Auth::guard('giangvien')->check() && Auth::guard('giangvien')->user()->vai_tro === 'admin')
                        <th>Hành động</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($sinhviens as $sv)
                    <tr>
                        <td><strong>{{ $sv->ma_sv }}</strong></td>
                        <td>{{ $sv->ho_ten }}</td>
                        <td><span class="badge bg-info">{{ $sv->lop }}</span></td>
                        <td>{{ $sv->email }}</td>
                        <td>
                            @if($sv->hinh_anh)
                                <img src="{{ asset($sv->hinh_anh) }}" 
                                    width="60" 
                                    height="60" 
                                    class="rounded-circle object-fit-cover hover-zoom" 
                                    style="object-fit: cover; cursor: pointer;"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#imageModal{{ $sv->id }}"
                                    title="Xem ảnh">
                            @else
                                <span class="text-muted small">Chưa có ảnh</span>
                            @endif
                        </td>
                        
                        @if(Auth::guard('giangvien')->check() && Auth::guard('giangvien')->user()->vai_tro === 'admin')
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('sinhvien.edit', $sv->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('sinhvien.destroy', $sv->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Xóa sinh viên {{ $sv->ho_ten }}?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ Auth::guard('giangvien')->check() && Auth::guard('giangvien')->user()->vai_tro === 'admin' ? 6 : 5 }}" class="text-center text-muted py-4">
                            <i class="fas fa-users-slash fa-2x mb-2"></i><br>
                            Không có sinh viên nào
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Table -->
    <div class="d-block d-md-none">
        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table table-bordered table-striped table-hover" style="min-width: 800px;">
                <thead class="table-dark">
                    <tr>
                        <th>Mã SV</th>
                        <th>Họ tên</th>
                        <th>Lớp</th>
                        <th>Email</th>
                        <th>Hình ảnh</th>
                        @if(Auth::guard('giangvien')->check() && Auth::guard('giangvien')->user()->vai_tro === 'admin')
                        <th>Hành động</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($sinhviens as $sv)
                    <tr>
                        <td><strong>{{ $sv->ma_sv }}</strong></td>
                        <td>{{ $sv->ho_ten }}</td>
                        <td><span class="badge bg-info">{{ $sv->lop }}</span></td>
                        <td>{{ $sv->email }}</td>
                        <td>
                            @if($sv->hinh_anh)
                                <img src="{{ asset($sv->hinh_anh) }}" 
                                    width="60" 
                                    height="60" 
                                    class="rounded-circle object-fit-cover hover-zoom" 
                                    style="object-fit: cover; cursor: pointer;"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#imageModal{{ $sv->id }}"
                                    title="Xem ảnh">
                            @else
                                <span class="text-muted small">Chưa có ảnh</span>
                            @endif
                        </td>
                        
                        @if(Auth::guard('giangvien')->check() && Auth::guard('giangvien')->user()->vai_tro === 'admin')
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('sinhvien.edit', $sv->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('sinhvien.destroy', $sv->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Xóa sinh viên {{ $sv->ho_ten }}?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ Auth::guard('giangvien')->check() && Auth::guard('giangvien')->user()->vai_tro === 'admin' ? 6 : 5 }}" class="text-center text-muted py-4">
                            <i class="fas fa-users-slash fa-2x mb-2"></i><br>
                            Không có sinh viên nào
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Hiển thị thông báo về cuộn ngang trên mobile -->
        <div class="alert alert-info mt-2 d-flex align-items-center" style="font-size: 12px;">
            <i class="fas fa-info-circle me-2"></i>
            <span>Vuốt sang trái/phải để xem thêm thông tin</span>
        </div>
    </div>


    @foreach($sinhviens as $sv)
        @if($sv->hinh_anh)
            <div class="modal fade" id="imageModal{{ $sv->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ảnh sinh viên: {{ $sv->ho_ten }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="{{ asset($sv->hinh_anh) }}" 
                                class="img-fluid rounded" 
                                alt="{{ $sv->ho_ten }}"
                                style="max-height: 70vh; object-fit: contain;">
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- Hiển thị phân trang --}}
    @if($sinhviens->hasPages())
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                {{-- Previous Page Link --}}
                @if($sinhviens->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $sinhviens->previousPageUrl() }}" aria-label="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @php
                    $current = $sinhviens->currentPage();
                    $last = $sinhviens->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                @endphp

                {{-- First Page Link --}}
                @if($start > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $sinhviens->url(1) }}">1</a>
                    </li>
                    @if($start > 2)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                @endif

                {{-- Page Number Links --}}
                @for($i = $start; $i <= $end; $i++)
                    @if($i == $current)
                        <li class="page-item active">
                            <span class="page-link">{{ $i }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $sinhviens->url($i) }}">{{ $i }}</a>
                        </li>
                    @endif
                @endfor

                {{-- Last Page Link --}}
                @if($end < $last)
                    @if($end < $last - 1)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $sinhviens->url($last) }}">{{ $last }}</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if($sinhviens->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $sinhviens->nextPageUrl() }}" aria-label="Next">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>

    {{-- Hiển thị thông tin kết quả --}}
    <div class="text-center text-muted mt-2 small">
        Hiển thị {{ ($sinhviens->currentPage() - 1) * $sinhviens->perPage() + 1 }} 
        đến {{ min($sinhviens->currentPage() * $sinhviens->perPage(), $sinhviens->total()) }} 
        của {{ $sinhviens->total() }} kết quả
    </div>
    @endif
</div>
@endsection
@push('styles')
<style>
    .table th {
        background-color: var(--primary-color);
        color: white;
        font-weight: 500;
    }
    
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: relative;
    }
    
    .badge {
        padding: 4px 8px;
        font-size: 12px;
    }
    
    .object-fit-cover {
        object-fit: cover;
    }
    
    /* Style cho pagination */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .page-link {
        color: var(--primary-color);
    }
    
    .page-link:hover {
        color: #0d62c9;
    }
    
    /* Mobile table scroll hint */
    .scroll-hint {
        position: sticky;
        left: 0;
        bottom: 10px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        animation: fadeOut 3s forwards;
        animation-delay: 2s;
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
</style>
@endpush
@push('styles')
<style>
    /* Pagination Styles */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-item {
        margin: 0 2px;
    }
    
    .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        color: var(--primary-color);
        border: 1px solid #dee2e6;
        border-radius: 4px;
        min-width: 32px;
        text-align: center;
        transition: all 0.2s;
    }
    
    .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
        color: var(--primary-color);
        transform: translateY(-1px);
    }
    
    .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        box-shadow: 0 2px 4px rgba(var(--primary-color-rgb, 13, 110, 253), 0.2);
    }
    
    .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    
    .pagination-sm .page-link {
        padding: 0.2rem 0.4rem;
        font-size: 0.75rem;
    }
    
    /* Hiệu ứng hover */
    .page-link i {
        font-size: 0.7rem;
    }
    
    /* Mobile responsive */
    @media (max-width: 576px) {
        .page-link {
            padding: 0.15rem 0.3rem;
            min-width: 28px;
            font-size: 0.75rem;
        }
        
        .pagination-sm .page-link {
            padding: 0.15rem 0.25rem;
            font-size: 0.7rem;
        }
    }
    
    /* Info text */
    .pagination-info {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
</style>
@endpush