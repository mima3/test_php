<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodoController extends Controller
{
    public function index(): View
    {
        $todos = $this->todoQuery()
            ->orderBy('is_completed')
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->get();

        return view('todos.index', [
            'todos' => $todos,
            'completedCount' => $todos->where('is_completed', true)->count(),
            'remainingCount' => $todos->where('is_completed', false)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
        ]);

        $request->user()->todos()->create($validated);

        return redirect()
            ->route('todos.index')
            ->with('status', 'TODOを追加しました。');
    }

    public function update(Request $request, Todo $todo): RedirectResponse
    {
        $this->ensureTodoOwnership($request, $todo);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
        ]);

        $todo->update($validated);

        return redirect()
            ->route('todos.index')
            ->with('status', 'TODOを更新しました。');
    }

    public function toggle(Todo $todo): RedirectResponse
    {
        abort_unless($todo->user_id === auth()->id(), 403);

        $todo->update([
            'is_completed' => ! $todo->is_completed,
        ]);

        return redirect()
            ->route('todos.index')
            ->with('status', 'TODOの状態を更新しました。');
    }

    public function destroy(Todo $todo): RedirectResponse
    {
        abort_unless($todo->user_id === auth()->id(), 403);

        $todo->delete();

        return redirect()
            ->route('todos.index')
            ->with('status', 'TODOを削除しました。');
    }

    protected function todoQuery(): Builder
    {
        return Todo::query()->where('user_id', auth()->id());
    }

    protected function ensureTodoOwnership(Request $request, Todo $todo): void
    {
        abort_unless($todo->user_id === $request->user()->id, 403);
    }
}
