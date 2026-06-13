<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\PlaystationUnit;
use App\Models\Rate;
use App\Models\Product;
use App\Models\OnsitePlayTransaction;
use Carbon\Carbon;

class PlayStationFoodBeverageTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $rate;
    protected $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);

        $this->rate = Rate::create([
            'service_type' => 'Di Tempat',
            'playstation_type' => 'PS5',
            'price' => 10000,
        ]);

        $this->unit = PlaystationUnit::create([
            'name' => 'PS5 Room A',
            'type' => 'PS5',
            'status' => 'Tersedia',
        ]);
    }

    public function test_product_crud_management()
    {
        // 1. Unauthenticated user cannot manage products
        $this->getJson('/admin/api/products')->assertStatus(401);

        // 2. Add product
        $response = $this->actingAs($this->admin)->postJson('/admin/api/products', [
            'name' => 'Indomie Rebus',
            'category' => 'Makanan',
            'price' => 8000,
            'stock' => 50,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('products', ['name' => 'Indomie Rebus', 'stock' => 50]);

        $productId = $response->json('data.id');

        // 3. Edit product
        $this->actingAs($this->admin)->putJson("/admin/api/products/{$productId}", [
            'name' => 'Indomie Rebus Spesial',
            'category' => 'Makanan',
            'price' => 10000,
            'stock' => 45,
        ])->assertStatus(200);

        $this->assertDatabaseHas('products', ['name' => 'Indomie Rebus Spesial', 'stock' => 45]);

        // 4. List products
        $listResponse = $this->actingAs($this->admin)->getJson('/admin/api/products');
        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json('data'));

        // 5. Delete product
        $this->actingAs($this->admin)->deleteJson("/admin/api/products/{$productId}")->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    public function test_adding_orders_to_active_play_and_verifying_total_cost()
    {
        // 1. Create a product
        $product = Product::create([
            'name' => 'Es Teh Manis',
            'category' => 'Minuman',
            'price' => 3000,
            'stock' => 100,
        ]);

        // 2. Start play
        $this->actingAs($this->admin)->postJson(route('dashboard.start-play'), [
            'playstation_unit_id' => $this->unit->id,
        ])->assertStatus(200);

        $this->unit->refresh();
        $this->assertEquals('Bermain', $this->unit->status);

        // 3. Place order
        $orderResponse = $this->actingAs($this->admin)->postJson(route('dashboard.add-order'), [
            'playstation_unit_id' => $this->unit->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $orderResponse->assertStatus(200);
        $orderResponse->assertJsonPath('success', true);

        // Verify stock decremented
        $product->refresh();
        $this->assertEquals(98, $product->stock);

        // Verify database records
        $transaction = OnsitePlayTransaction::where('playstation_unit_id', $this->unit->id)
            ->where('status', 'Berjalan')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertDatabaseHas('onsite_play_orders', [
            'onsite_play_transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => 6000,
        ]);

        // 4. Fast forward time and end play
        // We will simulate the play duration by updating the started_at manually in database
        $transaction->update([
            'started_at' => Carbon::now()->subHours(2), // 2 hours play = 2 * 10,000 = 20,000 play cost
        ]);

        $endResponse = $this->actingAs($this->admin)->postJson(route('dashboard.end-play'), [
            'playstation_unit_id' => $this->unit->id,
        ]);

        $endResponse->assertStatus(200);
        $endResponse->assertJsonPath('success', true);

        // Total cost should be 20,000 (play cost) + 6,000 (2x Es Teh Manis) = 26,000
        $endResponse->assertJsonPath('data.play_price', 'Rp 20.000');
        $endResponse->assertJsonPath('data.orders_price', 'Rp 6.000');
        $endResponse->assertJsonPath('data.total_price', 'Rp 26.000');

        // Verify unit status reset
        $this->unit->refresh();
        $this->assertEquals('Tersedia', $this->unit->status);
    }
}
