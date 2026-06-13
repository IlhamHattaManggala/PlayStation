<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\PlaystationUnit;
use App\Models\Rate;
use App\Models\OnsitePlayTransaction;
use App\Models\RentalTransaction;
use Carbon\Carbon;

class PlayStationBillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default admin user
        User::create([
            'name' => 'Admin Test',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);

        // Seed default rates
        Rate::create([
            'service_type' => 'Di Tempat',
            'playstation_type' => 'PS5',
            'price' => 12000,
        ]);

        Rate::create([
            'service_type' => 'Sewa PS',
            'playstation_type' => 'PS5',
            'price' => 120000,
        ]);

        Rate::create([
            'service_type' => 'Sewa Setengah Hari',
            'playstation_type' => 'PS5',
            'price' => 70000,
        ]);
    }

    public function test_login_validation_works()
    {
        // Failed login
        $response = $this->post('/admin/login', [
            'email' => 'admin@gmail.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        // Successful login
        $response = $this->post('/admin/login', [
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_billing_page_requires_auth_and_loads_successfully()
    {
        // 1. Guest is redirected to login
        $response = $this->get('/admin/billing');
        $response->assertRedirect('/admin/login');

        // 2. Auth user can access
        $admin = User::first();
        $response2 = $this->actingAs($admin)->get('/admin/billing');
        $response2->assertStatus(200);
        $response2->assertViewIs('billing');
    }

    public function test_playstation_cannot_be_occupied_by_two_active_transactions_simultaneously()
    {
        $admin = User::first();
        $unit = PlaystationUnit::create([
            'name' => 'PS5 VIP 1',
            'type' => 'PS5',
            'status' => 'Tersedia',
        ]);

        // 1. Start play for the unit (API request)
        $response = $this->actingAs($admin)
            ->postJson(route('dashboard.start-play'), [
                'playstation_unit_id' => $unit->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        $unit->refresh();
        $this->assertEquals('Bermain', $unit->status);

        // 2. Try starting another play on the same unit (must fail)
        $response2 = $this->actingAs($admin)
            ->postJson(route('dashboard.start-play'), [
                'playstation_unit_id' => $unit->id,
            ]);

        $response2->assertStatus(422);
        $response2->assertJsonPath('success', false);
    }

    public function test_duration_and_pricing_calculation_is_correct()
    {
        $admin = User::first();
        $unit = PlaystationUnit::create([
            'name' => 'PS5 VIP 2',
            'type' => 'PS5',
            'status' => 'Tersedia',
        ]);

        // Start play transaction
        $startedAt = Carbon::now()->subMinutes(90); // 1.5 hours ago
        $transaction = OnsitePlayTransaction::create([
            'playstation_unit_id' => $unit->id,
            'started_at' => $startedAt,
            'hourly_rate' => 12000,
            'status' => 'Berjalan',
        ]);
        
        $unit->update(['status' => 'Bermain']);

        // End play transaction
        $response = $this->actingAs($admin)
            ->postJson(route('dashboard.end-play'), [
                'playstation_unit_id' => $unit->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify transaction details
        $transaction->refresh();
        $this->assertEquals('Selesai', $transaction->status);
        $this->assertEquals(90, $transaction->duration_minutes);
        
        // 90 minutes / 60 * 12000 = 18000
        $this->assertEquals(18000.00, $transaction->total_price);

        $unit->refresh();
        $this->assertEquals('Tersedia', $unit->status);
    }

    public function test_rental_transaction_with_collateral_upload_and_fractional_days()
    {
        $admin = User::first();
        $unit = PlaystationUnit::create([
            'name' => 'PS5 Rental 1',
            'type' => 'PS5',
            'status' => 'Tersedia',
        ]);

        // Mock upload file
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('ktp.jpg');

        $response = $this->actingAs($admin)
            ->postJson('/admin/api/transactions/rental', [
                'playstation_unit_id' => $unit->id,
                'renter_name' => 'John Doe',
                'phone' => '0812999999',
                'identity_card' => $file,
                'rental_start_date' => Carbon::now()->toDateString(),
                'rental_days' => 1.5,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify database entry
        $this->assertDatabaseHas('rental_transactions', [
            'playstation_unit_id' => $unit->id,
            'renter_name' => 'John Doe',
            'phone' => '0812999999',
            'rental_days' => 1.5,
            'daily_rate' => 120000.00,
            'total_price' => 190000.00, // 1.0 * 120000 + 70000
            'status' => 'Disewa',
        ]);

        $transaction = RentalTransaction::where('playstation_unit_id', $unit->id)->first();
        $this->assertNotNull($transaction);
        $this->assertStringStartsWith('images/jaminan/jaminan_', $transaction->identity_card_path);
        $this->assertFileExists(public_path($transaction->identity_card_path));

        // Clean up
        if (file_exists(public_path($transaction->identity_card_path))) {
            unlink(public_path($transaction->identity_card_path));
        }

        $unit->refresh();
        $this->assertEquals('Disewa', $unit->status);
    }

    public function test_rental_transaction_with_tv_addition()
    {
        $admin = User::first();
        $unit = PlaystationUnit::create([
            'name' => 'PS5 Rental with TV',
            'type' => 'PS5',
            'status' => 'Tersedia',
        ]);

        // Seed default app settings
        \App\Models\AppSetting::create([
            'app_name' => 'Rental PlayStation',
            'tv_rental_price' => 15000
        ]);

        // Mock upload file
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('ktp.jpg');

        $response = $this->actingAs($admin)
            ->postJson('/admin/api/transactions/rental', [
                'playstation_unit_id' => $unit->id,
                'renter_name' => 'Jane Doe',
                'phone' => '0812888888',
                'identity_card' => $file,
                'rental_start_date' => Carbon::now()->toDateString(),
                'rental_days' => 2,
                'include_tv' => 'on', // Checkbox value
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify database entry: 2 days * 120000 = 240000, TV is free
        $this->assertDatabaseHas('rental_transactions', [
            'playstation_unit_id' => $unit->id,
            'renter_name' => 'Jane Doe',
            'phone' => '0812888888',
            'rental_days' => 2.0,
            'include_tv' => true,
            'tv_price' => 0.00,
            'daily_rate' => 120000.00,
            'total_price' => 240000.00,
            'status' => 'Disewa',
        ]);

        $transaction = RentalTransaction::where('playstation_unit_id', $unit->id)->orderBy('created_at', 'desc')->first();
        $this->assertNotNull($transaction);
        $this->assertStringStartsWith('images/jaminan/jaminan_', $transaction->identity_card_path);
        $this->assertFileExists(public_path($transaction->identity_card_path));

        // Clean up
        if (file_exists(public_path($transaction->identity_card_path))) {
            unlink(public_path($transaction->identity_card_path));
        }
    }
}


