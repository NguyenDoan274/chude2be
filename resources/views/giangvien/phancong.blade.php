@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h2>Phân công giảng viên: {{ $giangvien->ho_ten }}</h2>

    <div class="mb-3">
        <a href="{{ route('giangvien.index') }}" class="btn btn-secondary">Trở về danh sách giảng viên</a>
        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#assignedModal">
            Xem phân công
        </button>
    </div>

    {{-- Bộ lọc nâng cao --}}
    <form method="GET" action="{{ route('giangvien.phancong', $giangvien->id) }}" class="mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="ten_mon" value="{{ request('ten_mon') }}" class="form-control" placeholder="Tên môn">
            </div>
            <div class="col-md-2">
                <input type="text" name="phong" value="{{ request('phong') }}" class="form-control" placeholder="Phòng">
            </div>
            <div class="col-md-3">
                <input type="date" name="ngay" value="{{ request('ngay') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <input type="time" name="gio" value="{{ request('gio') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Lọc</button>
                @if(count(request()->all()) > 0)
                    <a href="{{ route('giangvien.phancong', $giangvien->id) }}" class="btn btn-outline-secondary w-100 mt-1">Xóa lọc</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Bảng danh sách lịch thi --}}
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Tên môn</th>
                <th>Ngày thi</th>
                <th>Giờ thi</th>
                <th>Phòng</th>
                <th>Giảng viên đã phân công</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lichthis as $lt)
            <tr>
                <td>{{ $lt->monHoc->ten_mon }}</td>
                <td>{{ $lt->ngay_thi }}</td>
                <td>{{ $lt->gio_thi }}</td>
                <td>{{ $lt->phong }}</td>
                <td>{{ $lt->giangviens->count() }} GV</td>
                <td>
                    <form class="assignForm" data-lichid="{{ $lt->id }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            Phân công
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Modal Xem phân công --}}
<div class="modal fade" id="assignedModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Lịch thi đã phân công cho {{ $giangvien->ho_ten }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Tên môn</th>
                            <th>Ngày</th>
                            <th>Giờ</th>
                            <th>Phòng</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($giangvien->lichthis as $lt)
                            <tr>
                                <td>{{ $lt->monHoc->ten_mon }}</td>
                                <td>{{ $lt->ngay_thi }}</td>
                                <td>{{ $lt->gio_thi }}</td>
                                <td>{{ $lt->phong }}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm btn-unassign"
                                        data-lichid="{{ $lt->id }}">
                                        Hủy
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Chưa được phân công lịch nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- JS xử lý phân công AJAX --}}
<script>
document.querySelectorAll('.assignForm').forEach(form => {
    form.addEventListener('submit', async function(e){
        e.preventDefault();
        const lichId = this.dataset.lichid;
        const csrfToken = this.querySelector('[name=_token]').value;

        const res = await fetch(`/giangvien/{{ $giangvien->id }}/assign/${lichId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const data = await res.json();

        if(data.status === 'success'){
            Swal.fire('Thành công', data.message, 'success').then(()=> location.reload());
        } else if(data.status === 'conflict'){
            Swal.fire('Trùng lịch', data.message, 'warning');
        } else {
            Swal.fire('Lỗi', 'Không thể phân công', 'error');
        }
    });
});
</script>
<script>
document.querySelectorAll('.btn-unassign').forEach(btn => {
    btn.addEventListener('click', async function () {
        const lichId = this.dataset.lichid;

        const confirm = await Swal.fire({
            title: 'Xác nhận',
            text: 'Bạn có chắc muốn hủy phân công lịch này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Có, hủy',
            cancelButtonText: 'Không'
        });

        if(!confirm.isConfirmed) return;

        const res = await fetch(
            `/giangvien/{{ $giangvien->id }}/unassign/${lichId}`, 
            {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }
        );

        const data = await res.json();

        if(data.status === 'success'){
            Swal.fire('Thành công', data.message, 'success')
                .then(()=> location.reload());
        } else {
            Swal.fire('Lỗi', 'Không thể hủy phân công', 'error');
        }
    });
});
</script>
@endsection
