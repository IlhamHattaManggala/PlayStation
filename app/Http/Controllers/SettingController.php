<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::first() ?? AppSetting::create([
            'app_name' => 'Rental PlayStation',
            'address' => 'Jl. Raya PlayStation No. 45',
            'phone' => '081234567890',
            'description' => 'Aplikasi manajemen rental PlayStation',
            'tv_rental_price' => 15000
        ]);

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = AppSetting::first();
        if (!$settings) {
            $settings = new AppSetting();
        }

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,ico|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,webp,ico|max:2048',
        ]);

        // Handlers for file uploads
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo) {
                if (file_exists(public_path($settings->logo))) {
                    unlink(public_path($settings->logo));
                }
                if (file_exists(public_path('storage/' . $settings->logo))) {
                    unlink(public_path('storage/' . $settings->logo));
                }
            }
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/logos'), $filename);
            $settings->logo = 'images/logos/' . $filename;
        }

        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            if ($settings->favicon) {
                if (file_exists(public_path($settings->favicon))) {
                    unlink(public_path($settings->favicon));
                }
                if (file_exists(public_path('storage/' . $settings->favicon))) {
                    unlink(public_path('storage/' . $settings->favicon));
                }
            }
            $file = $request->file('favicon');
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/logos'), $filename);
            $settings->favicon = 'images/logos/' . $filename;
        }

        // Update database
        $settings->app_name = $request->app_name;
        $settings->address = $request->address;
        $settings->phone = $request->phone;
        $settings->description = $request->description;
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => "Pengaturan aplikasi berhasil disimpan.",
            'data' => [
                'app_name' => $settings->app_name,
                'logo_url' => $settings->logo_url,
                'favicon_url' => $settings->favicon_url,
                'address' => $settings->address,
                'phone' => $settings->phone,
                'description' => $settings->description
            ]
        ]);
    }

    public function updateSecurity(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini salah.'
            ], 422);
        }

        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Kredensial admin berhasil diperbarui.'
        ]);
    }
}
