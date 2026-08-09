<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_staff_and_pin_is_hashed(): void
    {
        $admin = User::create(['name' => 'Admin', 'pin' => '1111', 'role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Budi', 'role' => 'cashier', 'pin' => '123456',
        ])->assertRedirect();

        $staff = User::where('name', 'Budi')->firstOrFail();
        $this->assertTrue(Hash::check('123456', $staff->pin));
        $this->assertDatabaseHas('activity_logs', ['action' => 'staff.created', 'subject_id' => $staff->id]);
    }

    public function test_admin_cannot_delete_admin_or_modify_admin(): void
    {
        $admin = User::create(['name' => 'Admin', 'pin' => '1111', 'role' => 'admin']);
        $otherAdmin = User::create(['name' => 'Other', 'pin' => '2222', 'role' => 'admin']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $otherAdmin))->assertForbidden();
        $this->assertNull($otherAdmin->fresh()->deleted_at);
    }

    public function test_inactive_staff_cannot_login(): void
    {
        $staff = User::create(['name' => 'Budi', 'pin' => '1234', 'role' => 'cashier', 'is_active' => false]);

        $this->post(route('pos.login.submit'), ['user_id' => $staff->id, 'pin' => '1234'])
            ->assertSessionHasErrors('pin');
        $this->assertGuest();
    }

    public function test_deleting_staff_is_soft_delete_and_preserves_record(): void
    {
        $admin = User::create(['name' => 'Admin', 'pin' => '1111', 'role' => 'admin']);
        $staff = User::create(['name' => 'Budi', 'pin' => '1234', 'role' => 'cashier']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $staff))->assertRedirect();
        $this->assertSoftDeleted('users', ['id' => $staff->id]);
    }
}
