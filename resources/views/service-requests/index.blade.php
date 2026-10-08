<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Requests | Daymark</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="content-wrap request-page">
        <header class="request-header">
            <div>
                <p class="date-label">Daymark workspace</p>
                <h1>Service requests</h1>
                <p class="subtitle">{{ auth()->user()->role === 'administrator' ? 'Review requests from all students.' : 'Your requests and their review status.' }}</p>
            </div>
            <a class="back-link" href="{{ route('tasks.index') }}">Back to tasks</a>
        </header>

        @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="errors" role="alert">{{ $errors->first() }}</div>@endif

        @if (auth()->user()->role === 'student')
            <section class="request-card">
                <h2>New request</h2>
                <form class="request-form" action="{{ route('requests.store') }}" method="POST">
                    @csrf
                    <label for="item_name">Item or service</label>
                    <input id="item_name" name="item_name" maxlength="150" value="{{ old('item_name') }}" required>

                    <label for="quantity">Quantity</label>
                    <input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" required>

                    <label for="purpose">Purpose</label>
                    <textarea id="purpose" name="purpose" required>{{ old('purpose') }}</textarea>

                    <button class="submit-button" type="submit">Submit request</button>
                </form>
            </section>
        @endif

        <section class="request-list" aria-label="Service requests">
            @forelse ($serviceRequests as $serviceRequest)
                <article class="request-card">
                    <div class="request-card-heading">
                        <div>
                            <h2><a href="{{ route('requests.show', $serviceRequest) }}">{{ $serviceRequest->item_name }}</a></h2>
                            @if (auth()->user()->role === 'administrator')
                                <p class="request-owner">{{ $serviceRequest->user?->name ?? 'Unassigned legacy request' }}</p>
                            @endif
                        </div>
                        <span class="request-status status-{{ $serviceRequest->status }}">{{ ucfirst($serviceRequest->status) }}</span>
                    </div>
                    <p>Quantity: {{ $serviceRequest->quantity }}</p>
                    @if (auth()->user()->role === 'administrator')
                        <form class="status-form" action="{{ route('requests.status', $serviceRequest) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label for="status-{{ $serviceRequest->id }}">Review status</label>
                            <select id="status-{{ $serviceRequest->id }}" name="status">
                                @foreach (['pending', 'approved', 'rejected'] as $status)
                                    <option value="{{ $status }}" @selected($serviceRequest->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <button class="submit-button" type="submit">Update status</button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="empty-state"><h2>No requests yet.</h2><p>Submitted requests will appear here.</p></div>
            @endforelse
        </section>
    </main>
</body>
</html>
