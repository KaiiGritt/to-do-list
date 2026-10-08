<?php

use App\Models\ServiceRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->studentA = User::query()->where('email', 'avery.student@example.test')->firstOrFail();
    $this->studentB = User::query()->where('email', 'jordan.student@example.test')->firstOrFail();
    $this->administrator = User::query()->where('email', 'morgan.admin@example.test')->firstOrFail();
});

test('T01 guests are redirected from request lists and details without seeing data', function () {
    $request = $this->studentA->serviceRequests()->firstOrFail();

    $this->get(route('requests.index'))
        ->assertRedirect(route('login'))
        ->assertDontSee($request->item_name);

    $this->get(route('requests.show', $request))
        ->assertRedirect(route('login'))
        ->assertDontSee($request->item_name);
});

test('T02 each student lists and opens only their own requests', function () {
    $requestA = $this->studentA->serviceRequests()->firstOrFail();
    $requestB = $this->studentB->serviceRequests()->firstOrFail();

    $this->actingAs($this->studentA)
        ->get(route('requests.index'))
        ->assertOk()
        ->assertSee($requestA->item_name)
        ->assertDontSee($requestB->item_name);
    $this->get(route('requests.show', $requestA))->assertOk();

    $this->actingAs($this->studentB)
        ->get(route('requests.index'))
        ->assertOk()
        ->assertSee($requestB->item_name)
        ->assertDontSee($requestA->item_name);
    $this->get(route('requests.show', $requestB))->assertOk();
});

test('T03 students are denied direct access to the other students records', function () {
    $requestA = $this->studentA->serviceRequests()->firstOrFail();
    $requestB = $this->studentB->serviceRequests()->firstOrFail();

    $this->actingAs($this->studentA)
        ->get(route('requests.show', $requestB))
        ->assertForbidden()
        ->assertDontSee($requestB->requester_email);

    $this->actingAs($this->studentB)
        ->get(route('requests.show', $requestA))
        ->assertForbidden()
        ->assertDontSee($requestA->requester_email);
});

test('T04 both students are denied status changes and the stored status remains unchanged', function () {
    $requestA = $this->studentA->serviceRequests()->firstOrFail();
    $requestB = $this->studentB->serviceRequests()->firstOrFail();

    $this->actingAs($this->studentA)
        ->patch(route('requests.status', $requestA), ['status' => 'approved'])
        ->assertForbidden();
    $this->patch(route('requests.status', $requestB), ['status' => 'approved'])->assertForbidden();

    $this->actingAs($this->studentB)
        ->patch(route('requests.status', $requestA), ['status' => 'approved'])
        ->assertForbidden();
    $this->patch(route('requests.status', $requestB), ['status' => 'approved'])->assertForbidden();

    expect($requestA->fresh()->status)->toBe('pending')
        ->and($requestB->fresh()->status)->toBe('pending');
});

test('T05 administrators can list all requests view details and set each allowed status', function () {
    $requestA = $this->studentA->serviceRequests()->firstOrFail();
    $requestB = $this->studentB->serviceRequests()->firstOrFail();

    $this->actingAs($this->administrator)
        ->get(route('requests.index'))
        ->assertOk()
        ->assertSee($requestA->item_name)
        ->assertSee($requestB->item_name)
        ->assertSee($this->studentA->name)
        ->assertSee($this->studentB->name);
    $this->get(route('requests.show', $requestA))->assertOk();
    $this->get(route('requests.show', $requestB))->assertOk();

    foreach (['approved', 'rejected', 'pending'] as $status) {
        $this->patch(route('requests.status', $requestA), ['status' => $status])->assertRedirect();
        expect($requestA->fresh()->status)->toBe($status);
    }
});

test('T06 invalid quantities and a blank item name are rejected without saving a request', function () {
    foreach ([
        ['item_name' => 'Test item', 'quantity' => 0],
        ['item_name' => 'Test item', 'quantity' => -1],
        ['item_name' => 'Test item', 'quantity' => 'not-an-integer'],
        ['item_name' => '', 'quantity' => 1],
        ['item_name' => 'Test item', 'quantity' => 1, 'purpose' => str_repeat('x', 2001)],
    ] as $invalidInput) {
        $this->actingAs($this->studentA)
            ->postJson(route('requests.store'), [
                ...$invalidInput,
                'purpose' => $invalidInput['purpose'] ?? 'Input validation test.',
            ])
            ->assertUnprocessable();
    }

    expect(ServiceRequest::query()->count())->toBe(2);
});

test('T07 forbidden student-supplied ownership identity status and role fields are rejected', function () {
    $request = $this->studentA->serviceRequests()->firstOrFail();

    foreach ([
        'user_id' => $this->studentB->id,
        'requester_name' => $this->studentB->name,
        'requester_email' => $this->studentB->email,
        'status' => 'approved',
        'is_admin' => true,
        'role' => 'administrator',
    ] as $field => $value) {
        $this->actingAs($this->studentA)
            ->postJson(route('requests.store'), [
                'item_name' => "Forged {$field}",
                'quantity' => 1,
                'purpose' => 'Forbidden-field validation test.',
                $field => $value,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    }

    expect(ServiceRequest::query()->count())->toBe(2)
        ->and($request->fresh()->user_id)->toBe($this->studentA->id)
        ->and($request->fresh()->status)->toBe('pending')
        ->and($this->studentB->fresh()->role)->toBe('student');
});

test('T08 markup is escaped in views and apostrophes persist as ordinary text', function () {
    $itemName = "<b>LAB3</b> O'Reilly";
    $purpose = "<b>LAB3</b> O'Reilly's equipment request.";

    $this->actingAs($this->studentA)
        ->post(route('requests.store'), [
            'item_name' => $itemName,
            'quantity' => 1,
            'purpose' => $purpose,
        ])
        ->assertRedirect(route('requests.index'));

    $request = ServiceRequest::query()->where('item_name', $itemName)->firstOrFail();

    expect($request->purpose)->toBe($purpose);

    $this->get(route('requests.show', $request))
        ->assertOk()
        ->assertSee($itemName)
        ->assertSee($purpose)
        ->assertDontSee($purpose, false);
});

test('T10 administrators cannot store an invalid status and the previous value remains unchanged', function () {
    $request = $this->studentA->serviceRequests()->firstOrFail();

    $this->actingAs($this->administrator)
        ->patchJson(route('requests.status', $request), ['status' => 'cancelled'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');

    expect($request->fresh()->status)->toBe('pending');
});
