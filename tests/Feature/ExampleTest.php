<?php
 
namespace Tests\Feature;
 
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\AppSetting;
 
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default settings required by the global app layout
        AppSetting::create([
            'app_name' => 'PlayStation Rental',
            'phone' => '081234567890',
            'address' => 'Jl. Kebon Jeruk No. 12',
            'description' => 'Rental PS Terbaik',
        ]);
    }

    /**
     * Test that guests are redirected to the login page.
     */
    public function test_the_application_redirects_guest_to_login(): void
    {
        $response = $this->get('/');
 
        $response->assertRedirect('/admin');
    }

    /**
     * Test that the login page returns a successful response.
     */
    public function test_login_page_returns_successful_response(): void
    {
        $response = $this->get('/admin/login');
 
        $response->assertStatus(200);
    }
}

