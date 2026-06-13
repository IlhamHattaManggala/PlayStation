<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlaystationUnit;
use App\Models\Rate;
use App\Models\OnsitePlayTransaction;
use App\Models\RentalTransaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function getMetricsData()
    {
        $today = Carbon::today();

        $totalUnits = PlaystationUnit::count();
        $availableUnits = PlaystationUnit::where('status', 'Tersedia')->count();
        $playingUnits = PlaystationUnit::where('status', 'Bermain')->count();
        $rentedUnits = PlaystationUnit::where('status', 'Disewa')->count();
        $maintenanceUnits = PlaystationUnit::where('status', 'Maintenance')->count();

        $onsiteTodayCount = OnsitePlayTransaction::whereDate('created_at', $today)->count();
        $rentalTodayCount = RentalTransaction::whereDate('created_at', $today)->count();
        $totalTransactionsToday = $onsiteTodayCount + $rentalTodayCount;

        $onsiteRevenueToday = OnsitePlayTransaction::where('status', 'Selesai')
            ->whereDate('ended_at', $today)
            ->sum('total_price');

        $rentalRevenueToday = RentalTransaction::whereDate('created_at', $today)
            ->sum('total_price');

        $totalRevenueToday = $onsiteRevenueToday + $rentalRevenueToday;

        return [
            'totalUnits' => $totalUnits,
            'availableUnits' => $availableUnits,
            'playingUnits' => $playingUnits,
            'rentedUnits' => $rentedUnits,
            'maintenanceUnits' => $maintenanceUnits,
            'totalTransactionsToday' => $totalTransactionsToday,
            'totalRevenueToday' => $totalRevenueToday,
            'totalRevenueTodayFormatted' => 'Rp ' . number_format($totalRevenueToday, 0, ',', '.'),
        ];
    }

    public function index()
    {
        $metrics = $this->getMetricsData();

        // Calculate daily revenue and transactions count for the last 7 days
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $label = $date->translatedFormat('d M'); // e.g., "13 Jun"
            
            $onsiteRevenue = OnsitePlayTransaction::where('status', 'Selesai')
                ->whereDate('ended_at', $date)
                ->sum('total_price');

            $rentalRevenue = RentalTransaction::whereDate('created_at', $date)
                ->sum('total_price');

            $onsiteTrxCount = OnsitePlayTransaction::whereDate('created_at', $date)->count();
            $rentalTrxCount = RentalTransaction::whereDate('created_at', $date)->count();
            $totalTrx = $onsiteTrxCount + $rentalTrxCount;

            $chartData[] = [
                'date' => $dateStr,
                'label' => $label,
                'revenue' => (float)($onsiteRevenue + $rentalRevenue),
                'transactions' => $totalTrx
            ];
        }

        // Fetch all PlayStation units with active transactions
        $units = PlaystationUnit::with([
            'onsitePlayTransactions' => function ($query) {
                $query->where('status', 'Berjalan');
            },
            'rentalTransactions' => function ($query) {
                $query->where('status', 'Disewa');
            }
        ])->orderBy('name')->get();

        // Fetch rates for popup forms
        $availableRentalUnits = PlaystationUnit::where('status', 'Tersedia')->get();

        return view('dashboard', array_merge($metrics, [
            'units' => $units,
            'availableRentalUnits' => $availableRentalUnits,
            'chartData' => $chartData
        ]));
    }

    public function billing()
    {
        $units = PlaystationUnit::with([
            'onsitePlayTransactions' => function ($query) {
                $query->where('status', 'Berjalan')->with('orders.product');
            },
            'rentalTransactions' => function ($query) {
                $query->where('status', 'Disewa');
            }
        ])->orderBy('name')->get();

        $availableRentalUnits = PlaystationUnit::where('status', 'Tersedia')->get();

        return view('billing', compact('units', 'availableRentalUnits'));
    }

    public function apiMetrics()
    {
        $metrics = $this->getMetricsData();

        $units = PlaystationUnit::with([
            'onsitePlayTransactions' => function ($query) {
                $query->where('status', 'Berjalan')->with('orders.product');
            },
            'rentalTransactions' => function ($query) {
                $query->where('status', 'Disewa');
            }
        ])->orderBy('name')->get();

        // Format dates and prices for easier frontend usage
        $unitsFormatted = $units->map(function ($unit) {
            $activeOnsite = $unit->onsitePlayTransactions->first();
            $activeRental = $unit->rentalTransactions->first();

            $formattedOnsite = null;
            if ($activeOnsite) {
                $formattedOnsite = [
                    'id' => $activeOnsite->id,
                    'started_at' => $activeOnsite->started_at->toIso8601String(),
                    'started_at_formatted' => $activeOnsite->started_at->format('H:i'),
                    'hourly_rate' => $activeOnsite->hourly_rate,
                    'orders' => $activeOnsite->orders->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'product_name' => $order->product->name ?? 'Produk Terhapus',
                            'quantity' => $order->quantity,
                            'total_price' => $order->total_price,
                            'total_price_formatted' => 'Rp ' . number_format($order->total_price, 0, ',', '.'),
                        ];
                    }),
                ];
            }

            $formattedRental = null;
            if ($activeRental) {
                $formattedRental = [
                    'id' => $activeRental->id,
                    'renter_name' => $activeRental->renter_name,
                    'phone' => $activeRental->phone,
                    'include_tv' => $activeRental->include_tv,
                    'rental_end_date_formatted' => $activeRental->rental_end_date->format('d M Y'),
                    'total_price_formatted' => 'Rp ' . number_format($activeRental->total_price, 0, ',', '.'),
                ];
            }

            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'type' => $unit->type,
                'status' => $unit->status,
                'description' => $unit->description,
                'active_onsite' => $formattedOnsite,
                'active_rental' => $formattedRental
            ];
        });

        return response()->json([
            'success' => true,
            'data' => array_merge($metrics, [
                'units' => $unitsFormatted
            ])
        ]);
    }

    public function startPlay(Request $request)
    {
        $request->validate([
            'playstation_unit_id' => 'required|exists:playstation_units,id',
            'started_at' => 'nullable|date',
        ]);

        $unit = PlaystationUnit::findOrFail($request->playstation_unit_id);

        if ($unit->status !== 'Tersedia') {
            return response()->json([
                'success' => false,
                'message' => "Unit {$unit->name} sedang tidak tersedia (Status: {$unit->status})."
            ], 422);
        }

        // Fetch rate for this PlayStation type
        $rate = Rate::where('service_type', 'Di Tempat')
            ->where('playstation_type', $unit->type)
            ->first();

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => "Tarif 'Di Tempat' untuk {$unit->type} belum diatur. Silakan atur tarif terlebih dahulu di menu Kelola Tarif."
            ], 422);
        }

        $startedAt = $request->has('started_at') ? Carbon::parse($request->started_at) : Carbon::now();

        // Start transaction
        $transaction = OnsitePlayTransaction::create([
            'playstation_unit_id' => $unit->id,
            'started_at' => $startedAt,
            'hourly_rate' => $rate->price,
            'status' => 'Berjalan',
        ]);

        // Update unit status
        $unit->update(['status' => 'Bermain']);

        return response()->json([
            'success' => true,
            'message' => "Billing untuk unit {$unit->name} berhasil dimulai.",
            'data' => [
                'transaction_id' => $transaction->id,
                'unit_name' => $unit->name,
                'started_at' => $transaction->started_at->format('Y-m-d H:i:s'),
                'hourly_rate' => number_format($transaction->hourly_rate, 0, ',', '.')
            ]
        ]);
    }

    public function endPlay(Request $request)
    {
        $request->validate([
            'playstation_unit_id' => 'required|exists:playstation_units,id',
            'ended_at' => 'nullable|date',
        ]);

        $unit = PlaystationUnit::findOrFail($request->playstation_unit_id);

        if ($unit->status !== 'Bermain') {
            return response()->json([
                'success' => false,
                'message' => "Unit {$unit->name} sedang tidak dalam status 'Bermain'."
            ], 422);
        }

        // Find running transaction
        $transaction = OnsitePlayTransaction::where('playstation_unit_id', $unit->id)
            ->where('status', 'Berjalan')
            ->with('orders')
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => "Transaksi aktif tidak ditemukan untuk unit {$unit->name}."
            ], 422);
        }

        $now = $request->has('ended_at') ? Carbon::parse($request->ended_at) : Carbon::now();
        $durationMinutes = (int) abs($now->diffInMinutes($transaction->started_at));
        $durationMinutes = max(1, $durationMinutes);
        
        // Calculate play cost based on duration
        $playPrice = ($durationMinutes / 60) * $transaction->hourly_rate;
        $playPrice = round($playPrice);

        // Sum product orders total cost
        $ordersSum = (float)$transaction->orders->sum('total_price');

        $totalPrice = $playPrice + $ordersSum;

        // Update transaction
        $transaction->update([
            'ended_at' => $now,
            'duration_minutes' => $durationMinutes,
            'total_price' => $totalPrice,
            'status' => 'Selesai',
        ]);

        // Reset unit status
        $unit->update(['status' => 'Tersedia']);

        // Format duration for display
        $hours = floor($durationMinutes / 60);
        $minutes = $durationMinutes % 60;
        $durationText = "";
        if ($hours > 0) {
            $durationText .= "{$hours} jam ";
        }
        $durationText .= "{$minutes} menit";

        return response()->json([
            'success' => true,
            'message' => "Billing unit {$unit->name} berhasil diselesaikan.",
            'data' => [
                'unit_name' => $unit->name,
                'started_at' => $transaction->started_at->format('d M Y, H:i'),
                'ended_at' => $transaction->ended_at->format('d M Y, H:i'),
                'duration_text' => $durationText,
                'hourly_rate' => 'Rp ' . number_format($transaction->hourly_rate, 0, ',', '.'),
                'play_price' => 'Rp ' . number_format($playPrice, 0, ',', '.'),
                'orders_price' => 'Rp ' . number_format($ordersSum, 0, ',', '.'),
                'total_price' => 'Rp ' . number_format($totalPrice, 0, ',', '.'),
            ]
        ]);
    }

    public function addOrder(Request $request)
    {
        $request->validate([
            'playstation_unit_id' => 'required|exists:playstation_units,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $unit = PlaystationUnit::findOrFail($request->playstation_unit_id);
        $transaction = OnsitePlayTransaction::where('playstation_unit_id', $unit->id)
            ->where('status', 'Berjalan')
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => "Unit {$unit->name} sedang tidak bermain atau tidak memiliki billing aktif."
            ], 422);
        }

        $product = \App\Models\Product::findOrFail($request->product_id);

        $order = \App\Models\OnsitePlayOrder::create([
            'onsite_play_transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'price' => $product->price,
            'total_price' => $product->price * $request->quantity,
        ]);

        if ($product->stock >= $request->quantity) {
            $product->decrement('stock', $request->quantity);
        }

        return response()->json([
            'success' => true,
            'message' => "Pesanan {$request->quantity}x {$product->name} berhasil ditambahkan ke unit {$unit->name}.",
            'data' => $order
        ]);
    }

    public function returnRental(Request $request)
    {
        $request->validate([
            'playstation_unit_id' => 'required|exists:playstation_units,id',
        ]);

        $unit = PlaystationUnit::findOrFail($request->playstation_unit_id);

        if ($unit->status !== 'Disewa') {
            return response()->json([
                'success' => false,
                'message' => "Unit {$unit->name} sedang tidak dalam status 'Disewa'."
            ], 422);
        }

        // Find active rental transaction
        $transaction = RentalTransaction::where('playstation_unit_id', $unit->id)
            ->where('status', 'Disewa')
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => "Transaksi sewa aktif tidak ditemukan untuk unit {$unit->name}."
            ], 422);
        }

        // Update transaction status
        $transaction->update(['status' => 'Dikembalikan']);

        // Reset unit status
        $unit->update(['status' => 'Tersedia']);

        return response()->json([
            'success' => true,
            'message' => "Unit {$unit->name} telah berhasil dikembalikan.",
            'data' => [
                'unit_name' => $unit->name,
                'renter_name' => $transaction->renter_name,
                'total_price' => 'Rp ' . number_format($transaction->total_price, 0, ',', '.'),
            ]
        ]);
    }
}
