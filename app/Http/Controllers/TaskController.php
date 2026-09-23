<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'all')->toString();
        $search = $request->string('search')->trim()->toString();

        $tasks = Task::query()
            ->when($filter === 'today', fn ($query) => $query->whereDate('due_date', today()))
            ->when($filter === 'upcoming', fn ($query) => $query->whereDate('due_date', '>', today())->whereNull('completed_at'))
            ->when($filter === 'completed', fn ($query) => $query->whereNotNull('completed_at'))
            ->when($filter === 'inbox', fn ($query) => $query->whereNull('completed_at'))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            }))
            ->orderByRaw('completed_at IS NOT NULL')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest()
            ->get();

        $counts = [
            'inbox' => Task::whereNull('completed_at')->count(),
            'today' => Task::whereDate('due_date', today())->whereNull('completed_at')->count(),
            'upcoming' => Task::whereDate('due_date', '>', today())->whereNull('completed_at')->count(),
            'completed' => Task::whereNotNull('completed_at')->count(),
        ];

        return view('welcome', compact('tasks', 'counts', 'filter', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        Task::create($data);

        return to_route('tasks.index')->with('status', 'Task added to your list.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        $task->update($data);

        return to_route('tasks.index')->with('status', 'Task updated.');
    }

    public function toggle(Task $task): RedirectResponse
    {
        $task->update(['completed_at' => $task->is_complete ? null : now()]);

        return back()->with('status', $task->is_complete ? 'Task reopened.' : 'Task completed.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return to_route('tasks.index')->with('status', 'Task deleted.');
    }
}