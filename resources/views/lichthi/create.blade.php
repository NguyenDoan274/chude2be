@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h2>Thêm lịch thi</h2>
    <a href="{{ route('lichthi.index') }}" class="btn btn-secondary">Trở lại</a>

    <form action="{{ route('lichthi.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Môn học</label>
            <select name="mon_hoc_id" class="select2" required style="width: 100%">
                <option value="">-- Chọn môn học --</option>
                @foreach($monhocs as $mon)
                    <option value="{{ $mon->id }}" 
                        {{ old('mon_hoc_id') == $mon->id ? 'selected' : '' }}>
                        {{ $mon->ten_mon }}
                    </option>
                @endforeach
            </select>
            @error('mon_hoc_id')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label>Ngày thi</label>
            <input type="date" name="ngay_thi" class="form-control" 
                value="{{ old('ngay_thi') }}" required>
            @error('ngay_thi')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label>Giờ thi</label>
            <input type="time" name="gio_thi" min="07:00" max="20:00" class="form-control" 
                value="{{ old('gio_thi') }}" required>
            @error('gio_thi')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label>Phòng</label>
            <div class="row g-2">
                
                <div class="col-md-1">
                    <select id="type" class="form-control" required>
                        <option value="">Chọn khu</option>
                        <option value="A" {{ old('phong_type') == 'A' || substr(old('phong', ''), 0, 1) == 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ old('phong_type') == 'B' || substr(old('phong', ''), 0, 1) == 'B' ? 'selected' : '' }}>B</option>
                        <option value="C" {{ old('phong_type') == 'C' || substr(old('phong', ''), 0, 1) == 'C' ? 'selected' : '' }}>C</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <select id="num1" class="form-control" required></select>
                </div>

                <div class="col-md-1">
                    <select id="num2" class="form-control" required></select>
                </div>

                <div class="col-md-1">
                    <select id="num3" class="form-control" required>
                        <option value=""></option>
                        @for ($i = 1; $i <= 9; $i++)
                            <option value="{{ $i }}" 
                                {{ old('phong_last') == $i || substr(old('phong', ''), -1) == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

            </div>

            <input type="hidden" name="phong" id="phong" value="{{ old('phong') }}">

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

            // Lấy giá trị phòng cũ (nếu có)
            const oldPhong = "{{ old('phong') }}";

            function initializeDropdowns() {
                // Nếu có giá trị phòng cũ, parse nó
                if (oldPhong && oldPhong.length >= 4) {
                    const typeVal = oldPhong.charAt(0);
                    const num1Val = oldPhong.charAt(1);
                    const num2Val = oldPhong.charAt(2);
                    const num3Val = oldPhong.charAt(3);
                    
                    // Set giá trị cho dropdowns
                    type.value = typeVal;
                    updateDropdowns();
                    
                    // Cần setTimeout để đợi dropdowns cập nhật
                    setTimeout(() => {
                        num1.value = num1Val;
                        num2.value = num2Val;
                        num3.value = num3Val;
                        updateHiddenValue();
                    }, 100);
                } else {
                    updateDropdowns();
                }
            }

            function updateDropdowns() {
                num1.innerHTML = "";
                num2.innerHTML = "";

                if (type.value === "A" || type.value === "B") {
                    // num1: 1-4
                    for (let i = 1; i <= 4; i++) {
                        const selected = (oldPhong && oldPhong.charAt(1) == i && (type.value === "A" || type.value === "B")) ? 'selected' : '';
                        num1.innerHTML += `<option value="${i}" ${selected}>${i}</option>`;
                    }
                    // num2: 0-1
                    for (let i = 0; i <= 1; i++) {
                        const selected = (oldPhong && oldPhong.charAt(2) == i && (type.value === "A" || type.value === "B")) ? 'selected' : '';
                        num2.innerHTML += `<option value="${i}" ${selected}>${i}</option>`;
                    }
                }

                if (type.value === "C") {
                    // num1: 1-9
                    for (let i = 1; i <= 9; i++) {
                        const selected = (oldPhong && oldPhong.charAt(1) == i && type.value === "C") ? 'selected' : '';
                        num1.innerHTML += `<option value="${i}" ${selected}>${i}</option>`;
                    }
                    // num2: luôn là 0
                    num2.innerHTML = `<option value="0" ${oldPhong && oldPhong.charAt(2) == '0' && type.value === "C" ? 'selected' : ''}>0</option>`;
                }

                updateHiddenValue();
            }

            function updateHiddenValue() {
                if (type.value && num1.value && num2.value && num3.value) {
                    phong.value = type.value + num1.value + num2.value + num3.value;
                }
            }

            type.onchange = updateDropdowns;
            num1.onchange = updateHiddenValue;
            num2.onchange = updateHiddenValue;
            num3.onchange = updateHiddenValue;

            // Khởi tạo dropdowns khi trang load
            document.addEventListener('DOMContentLoaded', initializeDropdowns);
        </script>

        <div class="mb-3">
            <label>Kỳ thi</label>
            <input list="kythi_list" name="ky_thi" class="form-control" 
                placeholder="Nhập hoặc chọn kỳ thi..." 
                value="{{ old('ky_thi') }}" required>

            <datalist id="kythi_list">
                <option value="Thi giữa kỳ HK1"></option>
                <option value="Thi cuối kỳ HK1"></option>
                <option value="Thi giữa kỳ HK2"></option>
                <option value="Thi cuối kỳ HK2"></option>
                <option value="Thi giữa kỳ HK3"></option>
                <option value="Thi cuối kỳ HK3"></option>
            </datalist>
            @error('ky_thi')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
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
            <select name="nam_hoc" class="form-control" required>
                @foreach($options as $opt)
                    <option 
                        value="{{ $opt }}"
                        {{ old('nam_hoc') == $opt ? 'selected' : '' }}
                    >
                        {{ $opt }}
                    </option>
                @endforeach
            </select>
            @error('nam_hoc')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        
        <button type="submit" class="btn btn-success">Thêm</button>
    </form>
</div>
@endsection