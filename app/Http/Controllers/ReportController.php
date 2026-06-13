<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OnsitePlayTransaction;
use App\Models\RentalTransaction;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function filter(Request $request)
    {
        $timeframe = $request->input('timeframe', 'month');
        $parsedDate = null;

        if ($timeframe === 'day') {
            $dateInput = $request->input('date');
            $parsedDate = $dateInput ? Carbon::parse($dateInput) : Carbon::today();
            $startDate = $parsedDate->copy()->startOfDay();
            $endDate = $parsedDate->copy()->endOfDay();
        } elseif ($timeframe === 'week') {
            $weekInput = $request->input('week'); // e.g. "2026-W24"
            if ($weekInput && preg_match('/^(\d{4})-W(\d{2})$/', $weekInput, $matches)) {
                $parsedDate = Carbon::now()->setISODate($matches[1], $matches[2])->startOfWeek();
            } else {
                $parsedDate = Carbon::today()->startOfWeek();
            }
            $startDate = $parsedDate->copy()->startOfWeek();
            $endDate = $parsedDate->copy()->endOfWeek();
        } elseif ($timeframe === 'year') {
            $yearInput = $request->input('year');
            $parsedDate = $yearInput ? Carbon::createFromFormat('Y', $yearInput) : Carbon::today();
            $startDate = $parsedDate->copy()->startOfYear();
            $endDate = $parsedDate->copy()->endOfYear();
        } else { // month
            $monthInput = $request->input('month');
            if ($monthInput) {
                $parsedDate = Carbon::createFromFormat('Y-m', $monthInput);
            } else {
                $parsedDate = Carbon::today();
            }
            $startDate = $parsedDate->copy()->startOfMonth();
            $endDate = $parsedDate->copy()->endOfMonth();
        }

        // 1. Gather aggregates for the selected timeframe
        $onsiteTrxs = OnsitePlayTransaction::with('playstationUnit')
            ->where('status', 'Selesai')
            ->whereBetween('ended_at', [$startDate, $endDate])
            ->get();

        $rentalTrxs = RentalTransaction::with('playstationUnit')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $onsiteCount = $onsiteTrxs->count();
        $onsiteRevenue = $onsiteTrxs->sum('total_price');

        $rentalCount = $rentalTrxs->count();
        $rentalRevenue = $rentalTrxs->sum('total_price');

        $totalRevenue = $onsiteRevenue + $rentalRevenue;
        $totalCount = $onsiteCount + $rentalCount;

        // 2. Prepare breakdown table data
        $breakdownData = [];

        if ($timeframe === 'day') {
            // For single day, list individual transactions that occurred
            // Onsite Play Transactions
            foreach ($onsiteTrxs as $trx) {
                $hours = floor($trx->duration_minutes / 60);
                $mins = $trx->duration_minutes % 60;
                $durText = $trx->duration_minutes ? ($hours > 0 ? "{$hours}j " : "") . "{$mins}m" : "-";

                $breakdownData[] = [
                    'date_formatted' => $trx->playstationUnit->name ?? 'Unit PS',
                    'day_name' => "Main di Tempat (Selesai - {$durText})",
                    'onsite_revenue' => (float)$trx->total_price,
                    'rental_revenue' => 0.0,
                    'total_revenue' => (float)$trx->total_price,
                    'onsite_revenue_formatted' => 'Rp ' . number_format($trx->total_price, 0, ',', '.'),
                    'rental_revenue_formatted' => 'Rp 0',
                    'total_revenue_formatted' => 'Rp ' . number_format($trx->total_price, 0, ',', '.'),
                ];
            }
            // Rental Transactions
            foreach ($rentalTrxs as $trx) {
                $breakdownData[] = [
                    'date_formatted' => $trx->playstationUnit->name ?? 'Unit PS',
                    'day_name' => "Sewa Bawa Pulang ({$trx->renter_name} - {$trx->rental_days} hari)",
                    'onsite_revenue' => 0.0,
                    'rental_revenue' => (float)$trx->total_price,
                    'total_revenue' => (float)$trx->total_price,
                    'onsite_revenue_formatted' => 'Rp 0',
                    'rental_revenue_formatted' => 'Rp ' . number_format($trx->total_price, 0, ',', '.'),
                    'total_revenue_formatted' => 'Rp ' . number_format($trx->total_price, 0, ',', '.'),
                ];
            }
        } elseif ($timeframe === 'week') {
            // Breakdown shows 7 days of the week:
            for ($i = 0; $i < 7; $i++) {
                $currentDate = $startDate->copy()->addDays($i);

                $dayOnsite = OnsitePlayTransaction::where('status', 'Selesai')
                    ->whereDate('ended_at', $currentDate)
                    ->sum('total_price');

                $dayRental = RentalTransaction::whereDate('created_at', $currentDate)
                    ->sum('total_price');

                $dayTotal = $dayOnsite + $dayRental;

                $breakdownData[] = [
                    'date_formatted' => $currentDate->format('d M Y'),
                    'day_name' => $currentDate->translatedFormat('l'),
                    'onsite_revenue' => (float)$dayOnsite,
                    'rental_revenue' => (float)$dayRental,
                    'total_revenue' => (float)$dayTotal,
                    'onsite_revenue_formatted' => 'Rp ' . number_format($dayOnsite, 0, ',', '.'),
                    'rental_revenue_formatted' => 'Rp ' . number_format($dayRental, 0, ',', '.'),
                    'total_revenue_formatted' => 'Rp ' . number_format($dayTotal, 0, ',', '.'),
                ];
            }
            $breakdownData = array_reverse($breakdownData);
        } elseif ($timeframe === 'year') {
            // Breakdown shows 12 months:
            for ($month = 1; $month <= 12; $month++) {
                $currentMonth = $startDate->copy()->month($month);
                $monthStart = $currentMonth->copy()->startOfMonth();
                $monthEnd = $currentMonth->copy()->endOfMonth();

                $monthOnsite = OnsitePlayTransaction::where('status', 'Selesai')
                    ->whereBetween('ended_at', [$monthStart, $monthEnd])
                    ->sum('total_price');

                $monthRental = RentalTransaction::whereBetween('created_at', [$monthStart, $monthEnd])
                    ->sum('total_price');

                $monthTotal = $monthOnsite + $monthRental;

                $breakdownData[] = [
                    'date_formatted' => $currentMonth->translatedFormat('F Y'),
                    'day_name' => "Bulan " . $month,
                    'onsite_revenue' => (float)$monthOnsite,
                    'rental_revenue' => (float)$monthRental,
                    'total_revenue' => (float)$monthTotal,
                    'onsite_revenue_formatted' => 'Rp ' . number_format($monthOnsite, 0, ',', '.'),
                    'rental_revenue_formatted' => 'Rp ' . number_format($monthRental, 0, ',', '.'),
                    'total_revenue_formatted' => 'Rp ' . number_format($monthTotal, 0, ',', '.'),
                ];
            }
            $breakdownData = array_reverse($breakdownData);
        } else { // month (default)
            $daysInMonth = $startDate->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = $startDate->copy()->day($day);
                
                // Onsite revenue ended on this day
                $dayOnsiteRevenue = OnsitePlayTransaction::where('status', 'Selesai')
                    ->whereDate('ended_at', $currentDate)
                    ->sum('total_price');

                // Rental revenue created on this day
                $dayRentalRevenue = RentalTransaction::whereDate('created_at', $currentDate)
                    ->sum('total_price');

                $dayTotal = $dayOnsiteRevenue + $dayRentalRevenue;

                if ($dayTotal > 0 || $currentDate->isPast() || $currentDate->isToday()) {
                    $breakdownData[] = [
                        'date_formatted' => $currentDate->format('d M Y'),
                        'day_name' => $currentDate->translatedFormat('l'),
                        'onsite_revenue' => (float)$dayOnsiteRevenue,
                        'rental_revenue' => (float)$dayRentalRevenue,
                        'total_revenue' => (float)$dayTotal,
                        'onsite_revenue_formatted' => 'Rp ' . number_format($dayOnsiteRevenue, 0, ',', '.'),
                        'rental_revenue_formatted' => 'Rp ' . number_format($dayRentalRevenue, 0, ',', '.'),
                        'total_revenue_formatted' => 'Rp ' . number_format($dayTotal, 0, ',', '.'),
                    ];
                }
            }

            // Reverse to show latest days first
            $breakdownData = array_reverse($breakdownData);
        }

        // Generate timeframe name for UI subtitle/meta
        $monthName = '';
        if ($timeframe === 'day') {
            $monthName = $parsedDate->translatedFormat('d F Y');
        } elseif ($timeframe === 'week') {
            $monthName = "Minggu ke-" . $parsedDate->weekOfYear . " (" . $startDate->translatedFormat('d M') . " - " . $endDate->translatedFormat('d M Y') . ")";
        } elseif ($timeframe === 'year') {
            $monthName = "Tahun " . $parsedDate->format('Y');
        } else {
            $monthName = $parsedDate->translatedFormat('F Y');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'month_name' => $monthName,
                'total_revenue' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
                'total_revenue_raw' => $totalRevenue,
                
                'onsite_count' => $onsiteCount,
                'onsite_revenue' => 'Rp ' . number_format($onsiteRevenue, 0, ',', '.'),
                'onsite_revenue_raw' => $onsiteRevenue,
                
                'rental_count' => $rentalCount,
                'rental_revenue' => 'Rp ' . number_format($rentalRevenue, 0, ',', '.'),
                'rental_revenue_raw' => $rentalRevenue,
                
                'total_transactions' => $totalCount,
                'daily_breakdown' => $breakdownData
            ]
        ]);
    }
}
