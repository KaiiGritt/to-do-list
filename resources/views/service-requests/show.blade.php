<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $serviceRequest->item_name }} | Daymark</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="content-wrap request-page">
        <a class="back-link" href="{{ route('requests.index') }}">Back to requests</a>
        <article class="request-card request-detail">
            <p class="date-label">Service request #{{ $serviceRequest->id }}</p>
            <h1>{{ $serviceRequest->item_name }}</h1>
            <p><strong>Requester:</strong> {{ $serviceRequest->requester_name }}</p>
            <p><strong>Email:</strong> {{ $serviceRequest->requester_email }}</p>
            <p><strong>Quantity:</strong> {{ $serviceRequest->quantity }}</p>
            <p><strong>Purpose:</strong> {{ $serviceRequest->purpose }}</p>
            <p><strong>Status:</strong> {{ ucfirst($serviceRequest->status) }}</p>
            @if (auth()->user()->role === 'administrator')
                <p><strong>Owner:</strong> {{ $serviceRequest->user?->name ?? 'Unassigned legacy request' }}</p>
                <form class="status-form" action="{{ route('requests.status', $serviceRequest) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <label for="status">Review status</label>
                    <select id="status" name="status">
                        @foreach (['pending', 'approved', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected($serviceRequest->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button class="submit-button" type="submit">Update status</button>
                </form>
            @endif
        </article>
    </main>
</body>
</html>
