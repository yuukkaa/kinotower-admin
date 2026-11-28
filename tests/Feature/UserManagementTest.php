<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_guest_cannot_access_users_list(): void
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_active_and_deleted_users(): void
    {
        $activeUser = User::factory()->create(['fio' => 'Иван Петров']);
        $deletedUser = User::factory()->create(['fio' => 'Сергей Сидоров']);
        $deletedUser->delete();

        $allResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.users.index'));

        $allResponse->assertOk();
        $allResponse->assertSee($activeUser->fio);
        $allResponse->assertSee($deletedUser->fio);

        $activeResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.users.index', ['status' => 'active']));

        $activeResponse->assertOk();
        $activeResponse->assertSee($activeUser->fio);
        $activeResponse->assertDontSee($deletedUser->fio);

        $trashedResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.users.index', ['status' => 'trashed']));

        $trashedResponse->assertOk();
        $trashedResponse->assertSee($deletedUser->fio);
        $trashedResponse->assertDontSee($activeUser->fio);
    }

    public function test_admin_can_soft_delete_and_restore_user(): void
    {
        $user = User::factory()->create(['fio' => 'Алексей Смирнов']);

        $deleteResponse = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.users.destroy', $user));

        $deleteResponse->assertRedirect(route('admin.users.index'));
        $deleteResponse->assertSessionHas('status');
        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $restoreResponse = $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.users.restore', $user));

        $restoreResponse->assertRedirect(route('admin.users.index'));
        $restoreResponse->assertSessionHas('status');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }
}
