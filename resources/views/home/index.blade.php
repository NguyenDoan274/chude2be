@extends('layouts.main-layout')

@section('title', 'Trang chủ - Danh sách phòng thi')

@section('content')
    <h2 style="margin-bottom: 16px;">📚 Danh sách phòng thi</h2>
    
    <div class="grid-container">
        @foreach($lichThis as $lich)
            @if($lich->trang_thai === 'dang_dien_ra')
            <a href="{{ route('diemdanh.show', $lich->id) }}" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ $lich->monHoc->ten_mon }}</h3>
                    </div>
                    <div class="card-body">
                        <p>📅 {{ \Carbon\Carbon::parse($lich->ngay_thi)->format('d/m/Y') }}</p>
                        <p>🕒 {{ $lich->gio_thi }}</p>
                        <p>🏫 Phòng: <strong>{{ $lich->phong }}</strong></p>
                    </div>
                </div>
            </a>
            @endif
        @endforeach
    </div>
@endsection

@push('styles')
<style>
    /* Style cho grid container và card */
    .grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
    
    .card {
        background-color: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        overflow: hidden;
        max-height: 170px;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .card-header {
        background-color: var(--primary-color);
        color: white;
        padding: 16px;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    .card-header h3 {
        font-size: 16px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .card-body {
        padding: 16px;
        color: var(--text-color);
        flex: 1;
    }

    .card-body p {
        font-size: 14px;
    }
    
    .card-footer {
        padding: 12px 16px;
        border-top: 1px solid var(--border-color);
        text-align: right;
    }

    .btn-view {
        background-color: var(--primary-color);
        color: white;
        text-decoration: none;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 14px;
        transition: background-color 0.2s;
    }

    .btn-view:hover {
        background-color: #0d62c9;
    }

    .card-link {
        text-decoration: none;
        color: inherit;
    }
    
    .card-link:hover .card {
        transform: translateY(-4px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.12);
    }

    @media (max-width: 1024px) {
        .grid-container {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .grid-container {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            padding: 0 8px;
        }
        
        .card {
            max-height: none;
            margin-bottom: 8px;
        }
        
        .card-header h3 {
            font-size: 15px;
            text-align: center;
        }
    }
    
    @media (max-width: 480px) {
        .grid-container {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }
</style>
@endpush