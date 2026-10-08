<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->role === 'administrator'
            || ($user->role === 'student' && (int) $serviceRequest->user_id === (int) $user->getKey());
    }

    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    public function updateStatus(User $user): bool
    {
        return $user->role === 'administrator';
    }
}
