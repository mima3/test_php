<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_top_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_user_can_create_a_todo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/todos', [
            'title' => '買い物に行く',
            'description' => '牛乳とパンを買う',
            'due_date' => '2026-03-20',
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'title' => '買い物に行く',
            'is_completed' => false,
        ]);
    }

    public function test_user_can_toggle_a_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create([
            'user_id' => $user->id,
            'is_completed' => false,
        ]);

        $this->actingAs($user);

        $response = $this->patch("/todos/{$todo->id}/toggle");

        $response->assertRedirect('/');

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'is_completed' => true,
        ]);
    }

    public function test_user_can_register_and_see_todo_page(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');

        $this->followRedirects($response)->assertSee('TODO一覧');
        $this->assertAuthenticated();
    }
}
