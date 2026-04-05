<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LichThi;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = LichThi::with('monHoc');
        
        if ($user && in_array($user->vai_tro, ['giang_vien', 'admin'])) {
            $query->whereHas('phanCongGVs', function ($q) use ($user) {
                $q->where('giang_vien_id', $user->id);
            });
        }
        
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('phong', 'like', "%$search%")
                ->orWhereHas('monHoc', fn($sub) => $sub->where('ten_mon', 'like', "%$search%"));
            });
        }

        $lichThis = $query->orderBy('ngay_thi', 'asc')
                    ->orderBy('gio_thi', 'asc')
                    ->paginate(10);

        foreach ($lichThis as $lichThi) {
            $lichThi->capNhatTrangThai();
        }

        return response()->json([
            'status' => 'success',
            'data' => $lichThis
        ], 200);
    }
}