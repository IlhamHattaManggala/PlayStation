<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Hash;

class PlayStationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the admin user
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);

        // Create initial settings
        AppSetting::create([
            'app_name' => 'Rental PlayStation',
            'address' => 'Jl. Raya PlayStation No. 45',
            'phone' => '081234567890',
            'description' => 'Aplikasi manajemen rental PlayStation',
            'tv_rental_price' => 15000
        ]);
    }

    public function test_guest_cannot_access_settings_or_update_security()
    {
        // 1. Guest cannot access settings index
        $this->get(route('settings.index'))->assertRedirect(route('login'));

        // 2. Guest cannot update security settings
        $this->postJson(route('settings.security'), [
            'email' => 'newadmin@gmail.com',
            'current_password' => 'password',
        ])->assertStatus(401);
    }

    public function test_admin_can_access_settings()
    {
        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertStatus(200)
            ->assertSee($this->admin->email);
    }

    public function test_admin_must_provide_correct_current_password()
    {
        // Attempt update with wrong current password
        $response = $this->actingAs($this->admin)->postJson(route('settings.security'), [
            'email' => 'updatedadmin@gmail.com',
            'current_password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Password saat ini salah.');

        // Verify email in database has not changed
        $this->admin->refresh();
        $this->assertEquals('admin@gmail.com', $this->admin->email);
    }

    public function test_admin_can_update_email_successfully()
    {
        $response = $this->actingAs($this->admin)->postJson(route('settings.security'), [
            'email' => 'updatedadmin@gmail.com',
            'current_password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify database updated
        $this->admin->refresh();
        $this->assertEquals('updatedadmin@gmail.com', $this->admin->email);
    }

    public function test_admin_can_update_password_successfully()
    {
        $response = $this->actingAs($this->admin)->postJson(route('settings.security'), [
            'email' => 'admin@gmail.com',
            'current_password' => 'password',
            'password' => 'newsecretpassword',
            'password_confirmation' => 'newsecretpassword',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Log out
        $this->post('/admin/logout');

        // Test login with old password fails
        $loginFail = $this->post('/admin/login', [
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);
        $loginFail->assertSessionHasErrors('email');
        $this->assertGuest();

        // Test login with new password succeeds
        $loginSuccess = $this->post('/admin/login', [
            'email' => 'admin@gmail.com',
            'password' => 'newsecretpassword',
        ]);
        $loginSuccess->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_admin_can_upload_logo_and_favicon()
    {
        $logo = \Illuminate\Http\UploadedFile::fake()->image('logo.png');
        $favicon = \Illuminate\Http\UploadedFile::fake()->image('favicon.png');

        $response = $this->actingAs($this->admin)->postJson('/admin/api/settings/update', [
            'app_name' => 'Updated Rental Name',
            'logo' => $logo,
            'favicon' => $favicon,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $settings = AppSetting::first();
        $this->assertStringStartsWith('images/logos/logo_', $settings->logo);
        $this->assertStringStartsWith('images/logos/favicon_', $settings->favicon);

        // Verify files exist in public folder
        $this->assertFileExists(public_path($settings->logo));
        $this->assertFileExists(public_path($settings->favicon));

        // Clean up
        if (file_exists(public_path($settings->logo))) {
            unlink(public_path($settings->logo));
        }
        if (file_exists(public_path($settings->favicon))) {
            unlink(public_path($settings->favicon));
        }
    }
}
