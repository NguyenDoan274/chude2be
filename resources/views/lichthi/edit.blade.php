@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h2>Sửa lịch thi</h2>
    <a href="{{ route('lichthi.index') }}" class="btn btn-secondary">Trở lại</a>
    <form action="{{ route('lichthi.update', $lichthi->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Môn học</label>
            <select name="mon_hoc_id" class="select2" required style="width: 100%">
                @foreach($monhocs as $mon)
                    <option value="{{ $mon->id }}" {{ $lichthi->mon_hoc_id == $mon->id ? 'selected' : '' }}>
                        {{ $mon->ten_mon }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Ngày thi</label>
            <input type="date" name="ngay_thi" value="{{ $lichthi->ngay_thi }}" class="form-control" required>
            @error('ngay_thi')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label>Giờ thi</label>
            <input type="time" name="gio_thi" value="{{ $lichthi->gio_thi }}" min="07:00" max="18:00" class="form-control" required>
            @error('gio_thi')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @php
            $phong = $lichthi->phong;   
            $typeOld = substr($phong, 0, 1);
            $n1Old = substr($phong, 1, 1);
            $n2Old = substr($phong, 2, 1);
            $n3Old = substr($phong, 3, 1);
        @endphp

        <div class="mb-3">
            <label>Phòng</label>

            <div class="row g-2">

                <!-- Ký tự đầu: A / B / C -->
                <div class="col-md-1">
                    <select id="type" class="form-control" required>
                        <option value="A" {{ $typeOld == 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ $typeOld == 'B' ? 'selected' : '' }}>B</option>
                        <option value="C" {{ $typeOld == 'C' ? 'selected' : '' }}>C</option>
                    </select>
                </div>

                <!-- Số 1 -->
                <div class="col-md-1">
                    <select id="num1" class="form-control" required></select>
                </div>

                <!-- Số 2 -->
                <div class="col-md-1">
                    <select id="num2" class="form-control" required></select>
                </div>

                <!-- Số 3 -->
                <div class="col-md-1">
                    <select id="num3" class="form-control" required>
                        @for ($i = 1; $i <= 9; $i++)
                            <option value="{{ $i }}" {{ $n3Old == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

            </div>

            <input type="hidden" name="phong" id="phong">

            @error('phong')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>


        <script>
            const type = document.getElementById('type');
            const num1 = document.getElementById('num1');
            const num2 = document.getElementById('num2');
            const num3 = document.getElementById('num3');
            const phong = document.getElementById('phong');

            const oldN1 = "{{ $n1Old }}";
            const oldN2 = "{{ $n2Old }}";

            function loadDropdowns() {
                num1.innerHTML = "";
                num2.innerHTML = "";

                if (type.value === "A" || type.value === "B") {
                    // num1: 1-4
                    for (let i = 1; i <= 4; i++) {
                        num1.innerHTML += `<option value="${i}" ${i == oldN1 ? 'selected' : ''}>${i}</option>`;
                    }
                    // num2: 0-1
                    for (let i = 0; i <= 1; i++) {
                        num2.innerHTML += `<option value="${i}" ${i == oldN2 ? 'selected' : ''}>${i}</option>`;
                    }
                } else if (type.value === "C") {
                    // num1: 1-9
                    for (let i = 1; i <= 9; i++) {
                        num1.innerHTML += `<option value="${i}" ${i == oldN1 ? 'selected' : ''}>${i}</option>`;
                    }
                    // num2: luôn là 0
                    num2.innerHTML = `<option value="0" ${oldN2 == 0 ? 'selected' : ''}>0</option>`;
                }

                updateHidden();
            }

            function updateHidden() {
                phong.value = type.value + num1.value + num2.value + num3.value;
            }

            type.onchange = loadDropdowns;
            num1.onchange = updateHidden;
            num2.onchange = updateHidden;
            num3.onchange = updateHidden;

            // Load dữ liệu có sẵn khi mở form
            loadDropdowns();
        </script>

       <div class="mb-3">
            <label>Kỳ thi</label>

            <input 
                list="kythi_list" 
                name="ky_thi" 
                class="form-control" 
                required
                value="{{ $lichthi->ky_thi }}"
                placeholder="Nhập hoặc chọn kỳ thi..." required>

            <datalist id="kythi_list">
                <option value="Thi giữa kỳ HK1"></option>
                <option value="Thi giữa kỳ HK2"></option>
                <option value="Thi cuối kỳ HK1"></option>
                <option value="Thi cuối kỳ HK2"></option>
                <option value="Thi giữa kỳ HK3"></option>
                <option value="Thi cuối kỳ HK3"></option>
            </datalist>
        </div>
       <div class="mb-3">
        <label>Năm học</label>
            @php
                $year = now()->year;
                $options = [
                    ($year - 1).'-'.$year,
                    $year.'-'.($year + 1)
                ];
            @endphp
            <select name="nam_hoc" value="{{ $lichthi->ky_thi }}" class="form-control" required>
                @foreach($options as $opt)
                    <option 
                        value="{{ $opt }}"
                        {{ old('nam_hoc', $lichthi->nam_hoc) == $opt ? 'selected' : '' }}
                    >
                        {{ $opt }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
    </form>
</div>
@endsection
