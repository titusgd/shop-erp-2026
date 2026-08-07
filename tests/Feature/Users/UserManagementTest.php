<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_page_requires_authentication(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_users_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('帳號管理');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $actor = User::factory()->create();
        $user = User::factory()->create([
            'name' => '店員甲',
            'username' => 'staff01',
        ]);

        $this->actingAs($actor)
            ->get(route('users.edit', $user))
            ->assertOk()
            ->assertSee('編輯帳號')
            ->assertSee('staff01');
    }

    public function test_users_can_be_listed_via_api(): void
    {
        $actor = User::factory()->create([
            'username' => 'actor',
            'email' => 'actor@example.com',
        ]);
        User::factory()->create([
            'name' => '店員甲',
            'username' => 'staff01',
            'email' => 'staff01@example.com',
        ]);

        $this->actingAs($actor)
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonFragment(['username' => 'staff01']);
    }

    public function test_users_can_be_searched_via_api(): void
    {
        $actor = User::factory()->create([
            'username' => 'actor',
            'email' => 'actor@example.com',
        ]);
        User::factory()->create([
            'name' => '店長',
            'username' => 'manager',
            'email' => 'manager@example.com',
        ]);

        $this->actingAs($actor)
            ->getJson('/api/users?search=manager')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.username', 'manager');
    }

    public function test_users_can_be_created_via_api(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->postJson('/api/users', [
                'name' => '新進店員',
                'username' => 'newbie',
                'email' => 'newbie@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertCreated()
            ->assertJsonPath('data.username', 'newbie');

        $this->assertDatabaseHas('users', [
            'username' => 'newbie',
            'email' => 'newbie@example.com',
        ]);
    }

    public function test_users_can_be_updated_via_api(): void
    {
        $actor = User::factory()->create();
        $user = User::factory()->create([
            'username' => 'oldname',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($actor)
            ->putJson("/api/users/{$user->id}", [
                'name' => '更新姓名',
                'username' => 'newname',
                'email' => 'new@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('data.username', 'newname');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'newname',
            'email' => 'new@example.com',
        ]);
    }

    public function test_users_can_be_deleted_via_api(): void
    {
        $actor = User::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($actor)
            ->deleteJson("/api/users/{$user->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_users_cannot_delete_themselves(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->deleteJson("/api/users/{$actor->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user']);

        $this->assertDatabaseHas('users', [
            'id' => $actor->id,
        ]);
    }
}
