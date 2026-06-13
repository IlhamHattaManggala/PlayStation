<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AppSetting;
use App\Models\Rate;
use App\Models\PlaystationUnit;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed default Admin
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Kasir',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Seed default App Settings
        AppSetting::updateOrCreate(
            ['id' => 1],
            [
                'app_name' => 'Rental PlayStation',
                'logo' => null,
                'favicon' => null,
                'address' => 'Jl. Raya PlayStation No. 45, Jakarta',
                'phone' => '081234567890',
                'description' => 'Aplikasi manajemen rental PlayStation',
                'tv_rental_price' => 15000
            ]
        );

        // 3. Seed default Rates
        $rates = [
            [
                'service_type' => 'Di Tempat',
                'playstation_type' => 'PS3',
                'price' => 5000,
                'description' => 'Tarif main PS3 di tempat per jam'
            ],
            [
                'service_type' => 'Di Tempat',
                'playstation_type' => 'PS4',
                'price' => 8000,
                'description' => 'Tarif main PS4 di tempat per jam'
            ],
            [
                'service_type' => 'Di Tempat',
                'playstation_type' => 'PS5',
                'price' => 12000,
                'description' => 'Tarif main PS5 di tempat per jam'
            ],
            [
                'service_type' => 'Sewa PS',
                'playstation_type' => 'PS3',
                'price' => 50000,
                'description' => 'Tarif sewa PS3 bawa pulang per hari'
            ],
            [
                'service_type' => 'Sewa PS',
                'playstation_type' => 'PS4',
                'price' => 75000,
                'description' => 'Tarif sewa PS4 bawa pulang per hari'
            ],
            [
                'service_type' => 'Sewa PS',
                'playstation_type' => 'PS5',
                'price' => 120000,
                'description' => 'Tarif sewa PS5 bawa pulang per hari'
            ],
            [
                'service_type' => 'Sewa Setengah Hari',
                'playstation_type' => 'PS3',
                'price' => 30000,
                'description' => 'Tarif sewa PS3 setengah hari bawa pulang'
            ],
            [
                'service_type' => 'Sewa Setengah Hari',
                'playstation_type' => 'PS4',
                'price' => 45000,
                'description' => 'Tarif sewa PS4 setengah hari bawa pulang'
            ],
            [
                'service_type' => 'Sewa Setengah Hari',
                'playstation_type' => 'PS5',
                'price' => 70000,
                'description' => 'Tarif sewa PS5 setengah hari bawa pulang'
            ],
        ];

        foreach ($rates as $rate) {
            Rate::updateOrCreate(
                [
                    'service_type' => $rate['service_type'],
                    'playstation_type' => $rate['playstation_type']
                ],
                $rate
            );
        }

        // 4. Seed default Playstation Units
        $units = [
            [
                'name' => 'PS3 A',
                'type' => 'PS3',
                'status' => 'Tersedia',
                'description' => 'Unit PS3 reguler'
            ],
            [
                'name' => 'PS3 B',
                'type' => 'PS3',
                'status' => 'Maintenance',
                'description' => 'Unit PS3 dalam perbaikan stik'
            ],
            [
                'name' => 'PS4 A',
                'type' => 'PS4',
                'status' => 'Tersedia',
                'description' => 'Unit PS4 reguler'
            ],
            [
                'name' => 'Room VIP 1 (PS5)',
                'type' => 'PS5',
                'status' => 'Tersedia',
                'description' => 'Sofa nyaman + TV 4K HDR 55 inch'
            ],
            [
                'name' => 'Room VIP 2 (PS5)',
                'type' => 'PS5',
                'status' => 'Tersedia',
                'description' => 'Sofa nyaman + TV 4K HDR 55 inch'
            ],
        ];

        foreach ($units as $unit) {
            PlaystationUnit::updateOrCreate(
                ['name' => $unit['name']],
                $unit
            );
        }

        // 5. Seed default Products (F&B)
        $products = [
            [
                'name' => 'Indomie Goreng + Telur',
                'category' => 'Makanan',
                'price' => 8000,
                'stock' => 50
            ],
            [
                'name' => 'Indomie Rebus + Telur',
                'category' => 'Makanan',
                'price' => 8000,
                'stock' => 50
            ],
            [
                'name' => 'Nasi Goreng Special',
                'category' => 'Makanan',
                'price' => 15000,
                'stock' => 20
            ],
            [
                'name' => 'Es Teh Manis',
                'category' => 'Minuman',
                'price' => 3000,
                'stock' => 100
            ],
            [
                'name' => 'Es Jeruk',
                'category' => 'Minuman',
                'price' => 4000,
                'stock' => 100
            ],
            [
                'name' => 'Kopi Susu',
                'category' => 'Minuman',
                'price' => 4000,
                'stock' => 100
            ],
            [
                'name' => 'Chitato',
                'category' => 'Jajanan',
                'price' => 5000,
                'stock' => 40
            ],
            [
                'name' => 'Kacang Atom',
                'category' => 'Jajanan',
                'price' => 3000,
                'stock' => 30
            ]
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(
                ['name' => $prod['name']],
                $prod
            );
        }
    }
}
