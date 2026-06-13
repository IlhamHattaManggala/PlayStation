<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlaystationUnit;
use App\Models\Rate;
use App\Models\OnsitePlayTransaction;
use App\Models\RentalTransaction;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index()
    {
        $units = PlaystationUnit::orderBy('name')->get();
        return view('transactions.index', compact('units'));
    }

    public function storeRental(Request $request)
    {
        $request->validate([
            'playstation_unit_id' => 'required|exists:playstation_units,id',
            'renter_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'identity_card' => 'required|file|image|max:2048',
            'rental_start_date' => 'required|date',
            'rental_days' => 'required|numeric|min:0.5|max:100',
            'include_tv' => 'nullable|string', // Checkbox sends "on" or can be cast via boolean
        ]);

        $unit = PlaystationUnit::findOrFail($request->playstation_unit_id);

        if ($unit->status !== 'Tersedia') {
            return response()->json([
                'success' => false,
                'message' => "Unit {$unit->name} sedang tidak tersedia untuk disewa."
            ], 422);
        }

        // Fetch rates for this unit's type
        $dailyRateModel = Rate::where('service_type', 'Sewa PS')
            ->where('playstation_type', $unit->type)
            ->first();

        $halfDayRateModel = Rate::where('service_type', 'Sewa Setengah Hari')
            ->where('playstation_type', $unit->type)
            ->first();

        if (!$dailyRateModel || !$halfDayRateModel) {
            return response()->json([
                'success' => false,
                'message' => "Tarif 'Sewa PS' dan 'Sewa Setengah Hari' untuk {$unit->type} belum diatur. Silakan atur tarif terlebih dahulu."
            ], 422);
        }

        $dailyPrice = (float)$dailyRateModel->price;
        $halfDayPrice = (float)$halfDayRateModel->price;

        $includeTv = $request->has('include_tv');

        // Store identity card file
        $path = null;
        if ($request->hasFile('identity_card')) {
            $file = $request->file('identity_card');
            $filename = 'jaminan_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/jaminan'), $filename);
            $path = 'images/jaminan/' . $filename;
        }

        // Calculate end date based on fractional days
        $start = Carbon::parse($request->rental_start_date);
        $hours = (float)$request->rental_days * 24;
        $end = $start->copy()->addHours($hours);

        $days = (float)$request->rental_days;
        $fullDays = floor($days);
        $hasHalfDay = ($days - $fullDays) > 0.1;

        $totalPrice = ($fullDays * $dailyPrice) + ($hasHalfDay ? $halfDayPrice : 0.0);

        // Create transaction
        $transaction = RentalTransaction::create([
            'playstation_unit_id' => $unit->id,
            'renter_name' => $request->renter_name,
            'phone' => $request->phone,
            'identity_card_path' => $path,
            'rental_start_date' => $start->toDateString(),
            'rental_end_date' => $end->toDateString(),
            'rental_days' => $days,
            'daily_rate' => $dailyPrice,
            'include_tv' => $includeTv,
            'tv_price' => 0.0,
            'total_price' => $totalPrice,
            'status' => 'Disewa',
        ]);

        // Update unit status
        $unit->update(['status' => 'Disewa']);

        return response()->json([
            'success' => true,
            'message' => "Sewa unit {$unit->name} untuk {$request->renter_name} berhasil didaftarkan.",
            'data' => $transaction
        ]);
    }

    public function filter(Request $request)
    {
        $date = $request->date; // Y-m-d format
        $type = $request->type; // 'Di Tempat', 'Sewa PS', or 'all'
        $status = $request->status; // depends on type
        $unitId = $request->playstation_unit_id; // unit ID or 'all'

        $formattedTrxs = [];

        // 1. Fetch Onsite Transactions if applicable
        if ($type === 'all' || $type === 'Di Tempat') {
            $query = OnsitePlayTransaction::with('playstationUnit');

            if ($date) {
                $query->whereDate('started_at', $date);
            }
            if ($unitId && $unitId !== 'all') {
                $query->where('playstation_unit_id', $unitId);
            }
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            $onsiteTrxs = $query->orderBy('created_at', 'desc')->get();

            foreach ($onsiteTrxs as $trx) {
                $hours = floor($trx->duration_minutes / 60);
                $mins = $trx->duration_minutes % 60;
                $durText = $trx->duration_minutes ? ($hours > 0 ? "{$hours}j " : "") . "{$mins}m" : "-";

                $formattedTrxs[] = [
                    'id' => $trx->id,
                    'type' => 'Di Tempat',
                    'unit_name' => $trx->playstationUnit->name ?? '-',
                    'customer' => 'Anonim',
                    'phone' => '-',
                    'start_time' => $trx->started_at->format('d M Y, H:i'),
                    'end_time' => $trx->ended_at ? $trx->ended_at->format('d M Y, H:i') : '-',
                    'duration_or_days' => $durText,
                    'rate' => 'Rp ' . number_format($trx->hourly_rate, 0, ',', '.'),
                    'total_price' => $trx->total_price ? 'Rp ' . number_format($trx->total_price, 0, ',', '.') : '-',
                    'total_price_raw' => $trx->total_price ?? 0,
                    'status' => $trx->status,
                    'created_at_raw' => $trx->created_at->toIso8601String()
                ];
            }
        }

        // 2. Fetch Rental Transactions if applicable
        if ($type === 'all' || $type === 'Sewa PS') {
            $query = RentalTransaction::with('playstationUnit');

            if ($date) {
                // If filter date, match if date is between start & end or equals start date
                $query->whereDate('rental_start_date', '<=', $date)
                      ->whereDate('rental_end_date', '>=', $date);
            }
            if ($unitId && $unitId !== 'all') {
                $query->where('playstation_unit_id', $unitId);
            }
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            $rentalTrxs = $query->orderBy('created_at', 'desc')->get();

            foreach ($rentalTrxs as $trx) {
                $formattedTrxs[] = [
                    'id' => $trx->id,
                    'type' => 'Sewa PS',
                    'unit_name' => $trx->playstationUnit->name ?? '-',
                    'customer' => $trx->renter_name,
                    'phone' => $trx->phone,
                    'identity_card_url' => $trx->identity_card_url,
                    'start_time' => $trx->rental_start_date->format('d M Y'),
                    'end_time' => $trx->rental_end_date->format('d M Y'),
                    'duration_or_days' => "{$trx->rental_days} hari",
                    'rate' => 'Rp ' . number_format($trx->daily_rate, 0, ',', '.'),
                    'total_price' => 'Rp ' . number_format($trx->total_price, 0, ',', '.'),
                    'total_price_raw' => $trx->total_price,
                    'status' => $trx->status,
                    'created_at_raw' => $trx->created_at->toIso8601String()
                ];
            }
        }

        // 3. Sort by created_at descending
        usort($formattedTrxs, function ($a, $b) {
            return strcmp($b['created_at_raw'], $a['created_at_raw']);
        });

        return response()->json([
            'success' => true,
            'data' => $formattedTrxs
        ]);
    }
}
