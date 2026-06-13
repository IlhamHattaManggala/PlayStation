<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlaystationUnit;

class PlaystationUnitController extends Controller
{
    public function index()
    {
        return view('units.index');
    }

    public function apiIndex()
    {
        $units = PlaystationUnit::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $units
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:playstation_units,name',
            'type' => 'required|string|in:PS3,PS4,PS5',
            'status' => 'required|string|in:Tersedia,Bermain,Disewa,Maintenance',
            'description' => 'nullable|string',
        ]);

        $unit = PlaystationUnit::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Unit PlayStation {$unit->name} berhasil ditambahkan.",
            'data' => $unit
        ]);
    }

    public function update(Request $request, $id)
    {
        $unit = PlaystationUnit::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:playstation_units,name,' . $unit->id,
            'type' => 'required|string|in:PS3,PS4,PS5',
            'status' => 'required|string|in:Tersedia,Bermain,Disewa,Maintenance',
            'description' => 'nullable|string',
        ]);

        // Guard: If status is changed from active status (Dipakai / Disewa) or to active status manually, prevent issues
        if (in_array($unit->status, ['Bermain', 'Disewa']) && $validated['status'] !== $unit->status) {
            return response()->json([
                'success' => false,
                'message' => "Status unit sedang aktif ({$unit->status}). Selesaikan transaksi terlebih dahulu melalui dashboard sebelum mengubah status."
            ], 422);
        }

        $unit->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Unit PlayStation {$unit->name} berhasil diperbarui.",
            'data' => $unit
        ]);
    }

    public function destroy($id)
    {
        $unit = PlaystationUnit::findOrFail($id);

        if (in_array($unit->status, ['Bermain', 'Disewa'])) {
            return response()->json([
                'success' => false,
                'message' => "Unit {$unit->name} tidak dapat dihapus karena sedang aktif digunakan dalam transaksi."
            ], 422);
        }

        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => "Unit PlayStation {$unit->name} berhasil dihapus."
        ]);
    }
}
