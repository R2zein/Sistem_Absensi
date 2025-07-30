<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\DB;

class DashboardStats extends BaseWidget
{
    // Fungsi untuk menampilkan stats card
    protected function getCards(): array
    {
        $user = auth()->user(); // Mendapatkan pengguna yang sedang login

        $cards = [];

        if ($user->hasAnyRole(['HR', 'admin'])) {
            // Jika pengguna adalah HR atau admin, tampilkan semua stats
            $cards[] = Card::make('Total Pengguna', DB::table('users')->count())
                ->description('Jumlah pengguna yang terdaftar')
                ->color('success');
            $cards[] = Card::make('Total Cuti Disetujui', DB::table('leaves')->where('status', 'approved')->count())
                ->description('Jumlah cuti yang sudah disetujui')
                ->color('success');
            $cards[] = Card::make('Total Cuti Belum Disetujui', DB::table('leaves')->where('status', 'pending')->count())
                ->description('Jumlah cuti yang belum disetujui')
                ->color('warning');
            $cards[] = Card::make('Total Kehadiran Terlambat', DB::table('attendances')->where('is_late', true)->count())
                ->description('Jumlah kehadiran terlambat')
                ->color('danger');
        } else {
            // Jika pengguna adalah user biasa, tampilkan hanya total miliknya
            $cards[] = Card::make('Total Kehadiran Anda', DB::table('attendances')->where('user_id', $user->id)->count())
                ->description('Jumlah kehadiran Anda')
                ->color('warning');
            $cards[] = Card::make('Total Cuti Anda', DB::table('leaves')->where('user_id', $user->id)->count())
                ->description('Jumlah permintaan cuti Anda')
                ->color('danger');
        }

        return $cards;
    }

    // Fungsi untuk data chart cuti terbanyak setiap bulan
    public function getLeaveProgressChartData(): array
    {
        $data = DB::table('leaves')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $chartData = array_fill(1, 12, 0);
        foreach ($data as $month => $count) {
            $chartData[$month] = $count;
        }

        return array_values($chartData);
    }

    // Fungsi untuk data chart presensi terlambat
    public function getLateAttendanceChartData(): array
    {
        $user = auth()->user(); // Mendapatkan pengguna yang sedang login

        $data = DB::table('attendances')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->where('is_late', true)
            ->when(!$user->hasAnyRole(['HR', 'admin']), function ($query) use ($user) {
                return $query->where('user_id', $user->id); // Filter untuk user biasa
            })
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Debugging: Tampilkan data yang diambil
        dd($data); // Hapus atau ganti dengan log setelah debugging

        $chartData = array_fill(1, 12, 0);
        foreach ($data as $month => $count) {
            $chartData[$month] = $count;
        }

        return array_values($chartData);
    }
}