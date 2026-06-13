<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rate;
use Illuminate\Validation\Rule;

class RateController extends Controller
{
    public function index()
    {
        return view('rates.index');
    }

    public function apiIndex()
    {
        $rates = Rate::orderBy('service_type')->orderBy('playstation_type')->get();
        return response()->json([
            'success' => true,
            'data' => $rates
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_type' => 'required|string|in:Di Tempat,Sewa PS',
            'playstation_type' => [
                'required',
                'string',
                'in:PS3,PS4,PS5',
                // Unique combination rule
                Rule::unique('rates')->where(function ($query) use ($request) {
                    return $query->where('service_type', $request->service_type)
                                 ->where('playstation_type', $request->playstation_type);
                })
            ],
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ], [
            'playstation_type.unique' => 'Tarif untuk kombinasi Layanan dan Tipe PS ini sudah terdaftar.'
        ]);

        $rate = Rate::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Tarif berhasil didaftarkan.",
            'data' => $rate
        ]);
    }

    public function update(Request $request, $id)
    {
        $rate = Rate::findOrFail($id);

        $validated = $request->validate([
            'service_type' => 'required|string|in:Di Tempat,Sewa PS',
            'playstation_type' => [
                'required',
                'string',
                'in:PS3,PS4,PS5',
                // Unique combination rule excluding current rate
                Rule::unique('rates')->where(function ($query) use ($request) {
                    return $query->where('service_type', $request->service_type)
                                 ->where('playstation_type', $request->playstation_type);
                })->ignore($rate->id)
            ],
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ], [
            'playstation_type.unique' => 'Tarif untuk kombinasi Layanan dan Tipe PS ini sudah terdaftar.'
        ]);

        $rate->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Tarif berhasil diperbarui.",
            'data' => $rate
        ]);
    }

    public function destroy($id)
    {
        $rate = Rate::findOrFail($id);
        $rate->delete();

        return response()->json([
            'success' => true,
            'message' => "Tarif berhasil dihapus."
        ]);
    }
}
