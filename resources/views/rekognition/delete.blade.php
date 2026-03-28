@extends('layouts.main-layout')

@section('content')
<div class="container mt-5">

    {{-- Hiển thị thông báo --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <a href="{{ route('home.index') }}" class="btn btn-secondary mt-2">Trở về</a>
    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            <h4>Xóa dữ liệu sinh viên khỏi Rekognition & S3</h4>
        </div>

        <div class="card-body">
            {{-- Form nhập MSSV --}}
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')

                <div class="form-group mb-3">
                    <label for="studentId">Mã số sinh viên (MSSV)</label>
                    <input type="text" id="studentId" name="studentId" class="form-control" placeholder="Nhập MSSV..." required>
                </div>

                <button type="submit" class="btn btn-danger">
                    Xóa sinh viên
                </button>
            </form>
        </div>
    </div>

</div>

{{-- Tự động gán action động theo MSSV --}}
<script>
    document.getElementById('deleteForm').addEventListener('submit', function(e) {
        let id = document.getElementById('studentId').value;

        // Gán đúng route: /rekognition/delete/{studentId}
        this.action = "/rekognition/delete/" + id;
    });
</script>

@endsection
