<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daymark | Your daily focus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('tasks.index') }}"><span class="brand-mark"><span></span><span></span><span></span></span><span>daymark</span></a>
            <div class="sidebar-section">
                <p class="eyebrow">Workspace</p>
                <nav class="side-nav" aria-label="Task filters">
                    @php($navItems = [['key' => 'inbox', 'label' => 'My tasks', 'icon' => '○'], ['key' => 'today', 'label' => 'Today', 'icon' => '◷'], ['key' => 'upcoming', 'label' => 'Upcoming', 'icon' => '⌁'], ['key' => 'completed', 'label' => 'Completed', 'icon' => '✓']])
                    @foreach ($navItems as $item)
                        <a href="{{ route('tasks.index', ['filter' => $item['key']]) }}" class="side-link {{ $filter === $item['key'] || ($filter === 'all' && $item['key'] === 'inbox') ? 'active' : '' }}"><span class="nav-icon">{{ $item['icon'] }}</span><span>{{ $item['label'] }}</span>@if (isset($counts[$item['key']]))<span class="count">{{ $counts[$item['key']] }}</span>@endif</a>
                    @endforeach
                </nav>
            </div>
            <div class="sidebar-note"><span class="note-spark">✦</span><strong>Small steps, clear days.</strong><p>Keep the list light and the next action obvious.</p></div>
            <div class="sidebar-footer"><span class="avatar">AS</span><div><strong>My workspace</strong><small>Personal plan</small></div><span class="more">•••</span></div>
        </aside>

        <main class="main-content">
            <header class="topbar"><div class="crumb"><span>Workspace</span><b>/</b><strong>{{ $filter === 'all' ? 'My tasks' : ucfirst($filter) }}</strong></div><form class="search" action="{{ route('tasks.index') }}" method="GET"><span aria-hidden="true">⌕</span><input type="search" name="search" value="{{ $search }}" placeholder="Search tasks" aria-label="Search tasks">@if ($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif</form><form method="POST" action="{{ route('logout') }}">@csrf<button class="sign-out-button" type="submit">Sign out</button></form></header>
            <section class="content-wrap">
                <div class="page-heading"><div><p class="date-label">{{ now()->format('l, F j') }}</p><h1>{{ $filter === 'all' ? 'Your focus list' : ucfirst($filter) }}</h1><p class="subtitle">{{ $counts['inbox'] }} {{ Str::plural('task', $counts['inbox']) }} waiting for your attention.</p></div><button class="primary-button" type="button" onclick="document.getElementById('title').focus()"><span>+</span> Add task</button></div>
                @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif
                <section class="composer" aria-label="Add a task"><form action="{{ route('tasks.store') }}" method="POST">@csrf<div class="composer-line"><span class="empty-check"></span><input id="title" name="title" placeholder="What needs to be done?" value="{{ old('title') }}" required></div><div class="composer-options"><input name="notes" placeholder="Add a note (optional)" value="{{ old('notes') }}"><label><span>Due</span><input type="date" name="due_date" value="{{ old('due_date') }}"></label><label><span>Priority</span><select name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></label><button class="submit-button" type="submit">Create task</button></div></form></section>
                @if ($errors->any())<div class="errors">{{ $errors->first() }}</div>@endif
                <div class="list-toolbar"><span>{{ $tasks->count() }} {{ Str::plural('result', $tasks->count()) }}</span><span class="sort-label">Sorted by <strong>date added</strong>⌄</span></div>
                <section class="task-list" aria-label="Tasks">
                    @forelse ($tasks as $task)
                        <article class="task-row {{ $task->is_complete ? 'is-complete' : '' }}"><form action="{{ route('tasks.toggle', $task) }}" method="POST" class="check-form">@csrf @method('PATCH')<button class="task-check" type="submit" aria-label="{{ $task->is_complete ? 'Reopen' : 'Complete' }} {{ $task->title }}">{{ $task->is_complete ? '✓' : '' }}</button></form><div class="task-body"><div class="task-title-line"><h2>{{ $task->title }}</h2><span class="priority priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span></div>@if ($task->notes)<p>{{ $task->notes }}</p>@endif<div class="task-meta"><span class="meta-icon">◷</span>{{ $task->due_date ? $task->due_date->format('M j, Y') : 'No due date' }} @if ($task->is_complete)<span class="completed-label">Completed {{ $task->completed_at->format('M j') }}</span>@endif</div></div><details class="task-menu"><summary aria-label="Edit task">•••</summary><div class="edit-popover"><form action="{{ route('tasks.update', $task) }}" method="POST">@csrf @method('PUT')<input name="title" value="{{ $task->title }}" required><input name="notes" value="{{ $task->notes }}" placeholder="Note"><input type="date" name="due_date" value="{{ optional($task->due_date)->format('Y-m-d') }}"><select name="priority"><option value="low" @selected($task->priority === 'low')>Low</option><option value="medium" @selected($task->priority === 'medium')>Medium</option><option value="high" @selected($task->priority === 'high')>High</option></select><button type="submit">Save changes</button></form><form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?')">@csrf @method('DELETE')<button class="delete-button" type="submit">Delete task</button></form></div></details></article>
                    @empty
                        <div class="empty-state"><span>✦</span><h2>A clear runway.</h2><p>No tasks here yet. Add the next thing you want to make happen.</p></div>
                    @endforelse
                </section>
            </section>
        </main>
    </div>
</body>
</html>
