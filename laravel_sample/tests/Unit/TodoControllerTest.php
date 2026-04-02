<?php

namespace Tests\Unit;

use App\Http\Controllers\TodoController;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TodoControllerTest extends TestCase
{
    public function test_index_returns_todo_view_with_summary_counts(): void
    {
        $todos = new Collection([
            new Todo(['title' => 'todo 1', 'is_completed' => false]),
            new Todo(['title' => 'todo 2', 'is_completed' => true]),
            new Todo(['title' => 'todo 3', 'is_completed' => false]),
        ]);

        /** @var Builder&MockInterface $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('orderBy')->once()->with('is_completed')->andReturnSelf();
        $builder->shouldReceive('orderByRaw')->once()->with('due_date is null')->andReturnSelf();
        $builder->shouldReceive('orderBy')->once()->with('due_date')->andReturnSelf();
        $builder->shouldReceive('orderByDesc')->once()->with('created_at')->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($todos);

        $controller = new class($builder) extends TodoController
        {
            public function __construct(private readonly Builder $builder) {}

            protected function todoQuery(): Builder
            {
                return $this->builder;
            }
        };

        $response = $controller->index();

        $this->assertSame('todos.index', $response->name());
        $this->assertSame($todos, $response->getData()['todos']);
        $this->assertSame(1, $response->getData()['completedCount']);
        $this->assertSame(2, $response->getData()['remainingCount']);
    }

    public function test_store_validates_input_and_creates_todo_for_authenticated_user(): void
    {
        $validated = [
            'title' => '買い物に行く',
            'description' => '牛乳とパンを買う',
            'due_date' => '2026-03-20',
        ];

        $request = Mockery::mock(Request::class);
        $user = Mockery::mock();
        $relation = Mockery::mock();

        $request->shouldReceive('validate')->once()->andReturn($validated);
        $request->shouldReceive('user')->once()->andReturn($user);
        $user->shouldReceive('todos')->once()->andReturn($relation);
        $relation->shouldReceive('create')->once()->with($validated);

        $response = app(TodoController::class)->store($request);

        $this->assertSame(route('todos.index'), $response->getTargetUrl());
        $this->assertSame('TODOを追加しました。', session('status'));
    }

    public function test_update_validates_input_checks_ownership_and_updates_todo(): void
    {
        $validated = [
            'title' => '更新後のTODO',
            'description' => '説明',
            'due_date' => '2026-03-21',
        ];

        $request = Mockery::mock(Request::class);
        $user = new User();
        $user->id = 10;

        /** @var Todo&MockInterface $todo */
        $todo = Mockery::mock(Todo::class)->makePartial();
        $todo->user_id = 10;
        $todo->shouldReceive('update')->once()->with($validated);

        $request->shouldReceive('user')->once()->andReturn($user);
        $request->shouldReceive('validate')->once()->andReturn($validated);

        $response = app(TodoController::class)->update($request, $todo);

        $this->assertSame(route('todos.index'), $response->getTargetUrl());
        $this->assertSame('TODOを更新しました。', session('status'));
    }

    public function test_toggle_flips_completion_flag_for_owned_todo(): void
    {
        $user = new User();
        $user->id = 20;
        $this->actingAs($user);

        /** @var Todo&MockInterface $todo */
        $todo = Mockery::mock(Todo::class)->makePartial();
        $todo->id = 1;
        $todo->user_id = 20;
        $todo->is_completed = false;
        $todo->shouldReceive('update')->once()->with([
            'is_completed' => true,
        ]);

        $response = app(TodoController::class)->toggle($todo);

        $this->assertSame(route('todos.index'), $response->getTargetUrl());
        $this->assertSame('TODOの状態を更新しました。', session('status'));
    }

    public function test_destroy_deletes_owned_todo(): void
    {
        $user = new User();
        $user->id = 30;
        $this->actingAs($user);

        /** @var Todo&MockInterface $todo */
        $todo = Mockery::mock(Todo::class)->makePartial();
        $todo->id = 2;
        $todo->user_id = 30;
        $todo->shouldReceive('delete')->once();

        $response = app(TodoController::class)->destroy($todo);

        $this->assertSame(route('todos.index'), $response->getTargetUrl());
        $this->assertSame('TODOを削除しました。', session('status'));
    }
}
