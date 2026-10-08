<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $serviceRequests = ServiceRequest::query()
            ->when(
                $request->user()->role !== 'administrator',
                fn ($query) => $query->where('user_id', $request->user()->id),
            )
            ->with('user')
            ->latest()
            ->get();

        return view('service-requests.index', compact('serviceRequests'));
    }

    public function show(ServiceRequest $serviceRequest): View
    {
        $this->authorize('view', $serviceRequest);

        return view('service-requests.show', compact('serviceRequest'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ServiceRequest::class);

        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:150'],
            'quantity' => ['required', 'integer', 'min:1'],
            'purpose' => ['required', 'string', 'max:2000'],
            'user_id' => ['prohibited'],
            'requester_name' => ['prohibited'],
            'requester_email' => ['prohibited'],
            'status' => ['prohibited'],
            'is_admin' => ['prohibited'],
            'role' => ['prohibited'],
        ]);

        $request->user()->serviceRequests()->create([
            ...$data,
            'requester_name' => $request->user()->name,
            'requester_email' => $request->user()->email,
            'status' => 'pending',
        ]);

        return to_route('requests.index')->with('status', 'Request submitted for review.');
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->authorize('updateStatus', ServiceRequest::class);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $serviceRequest->update($data);

        return back()->with('status', 'Request status updated.');
    }
}
