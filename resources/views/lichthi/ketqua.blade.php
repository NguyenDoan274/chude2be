@extends('layouts.main-layout')

@section('content')
<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0" style="font-size:1.2rem">
            📊 Kết quả điểm danh - Phòng {{ $lichThi->phong }} – {{ $lichThi->monHoc->ten_mon }}
        </h2>
        <div>
            <a href="{{ route('lichthi.export', $lichThi->id) }}" class="btn btn-success btn-sm">
                ⬇️ Xuất danh sách Excel
            </a>
            <a href="{{ route('lichthi.index') }}" class="btn btn-secondary btn-sm">
                ← Trở về danh sách phòng thi
            </a>
        </div>
    </div>

    {{-- Thông tin phòng thi --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Môn học:</strong> {{ $lichThi->monHoc->ten_mon }}
                </div>
                <div class="col-md-4">
                    <strong>Phòng thi:</strong> {{ $lichThi->phong }}
                </div>
                <div class="col-md-4">
                    <strong>Thời gian thi:</strong> {{ $lichThi->thoi_gian_thi->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>

    @if ($sinhViens->isEmpty())
        <div class="alert alert-warning">Không có sinh viên nào trong phòng thi này.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Mã SV</th>
                        <th>Họ tên</th>
                        <th>Lớp</th>
                        <th>Kết quả</th>
                        <th>Độ chính xác</th>
                        <th>Thời gian điểm danh</th>
                        <th>Hình thức</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sinhViens as $diemDanh)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $diemDanh->sinhVien->ma_sv }}</td>
                        <td>{{ $diemDanh->sinhVien->ho_ten }}</td>
                        <td>{{ $diemDanh->sinhVien->lop }}</td>
                        <td>
                            @if($diemDanh->ket_qua === 'hợp lệ')
                                <span class="badge bg-success">Có mặt</span>
                            @else
                                <span class="badge bg-danger">Vắng mặt</span>
                            @endif
                        </td>
                        <td>{{ $diemDanh->do_chinh_xac ?? '-' }}</td>
                        <td>{{ $diemDanh->thoi_gian_dd ?? '-' }}</td>
                        <td>{{ $diemDanh->hinh_thuc_dd ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Thống kê --}}
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">Tổng số SV</h5>
                        <p class="card-text h4">{{ $sinhViens->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Có mặt</h5>
                        <p class="card-text h4">{{ $sinhViens->where('ket_qua', 'hợp lệ')->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body text-center">
                        <h5 class="card-title">Vắng mặt</h5>
                        <p class="card-text h4">{{ $sinhViens->where('ket_qua', '!=', 'hợp lệ')->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <h5 class="card-title">Tỷ lệ có mặt</h5>
                        <p class="card-text h4">
                            {{ number_format(($sinhViens->where('ket_qua', 'hợp lệ')->count() / $sinhViens->count()) * 100, 1) }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection