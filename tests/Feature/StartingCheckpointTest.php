<?php

use App\Models\ServiceRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('the trusted seeder creates the demo accounts and student-owned requests', function () {
    $this->seed(DatabaseSeeder::class);

    $avery = User::query()->where('email', 'avery.student@example.test')->firstOrFail();
    $jordan = User::query()->where('email', 'jordan.student@example.test')->firstOrFail();
    $administrator = User::query()->where('email', 'morgan.admin@example.test')->firstOrFail();

    expect($avery->role)->toBe('student')
        ->and($jordan->role)->toBe('student')
        ->and($administrator->role)->toBe('administrator')
        ->and(User::query()->count())->toBe(3)
        ->and(ServiceRequest::query()->count())->toBe(2);

    expect($avery->serviceRequests()->firstOrFail()->user_id)->toBe($avery->id)
        ->and($jordan->serviceRequests()->firstOrFail()->user_id)->toBe($jordan->id)
        ->and(ServiceRequest::query()->firstOrFail()->user)->toBeInstanceOf(User::class);
});

test('seeding preserves existing requests and can be rerun', function () {
    $legacyRequestId = DB::table('requests')->insertGetId([
        'requester_name' => 'Existing Lab 2 Student',
        'requester_email' => 'lab2.student@example.test',
        'item_name' => 'Existing lab equipment',
        'quantity' => 2,
        'purpose' => 'Previously recorded Laboratory 2 request.',
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(ServiceRequest::query()->count())->toBe(3)
        ->and(DB::table('requests')->where('id', $legacyRequestId)->value('requester_name'))
        ->toBe('Existing Lab 2 Student')
        ->and(DB::table('requests')->where('id', $legacyRequestId)->value('user_id'))
        ->toBeNull();
});

test('rolling back and reapplying the preparation migration preserves Laboratory 2 requests', function () {
    $requestId = DB::table('requests')->insertGetId([
        'requester_name' => 'Existing Lab 2 Student',
        'requester_email' => 'lab2.student@example.test',
        'item_name' => 'Existing lab equipment',
        'quantity' => 2,
        'purpose' => 'Previously recorded Laboratory 2 request.',
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration = require database_path('migrations/2026_10_08_000000_add_roles_and_request_ownership.php');
    $migration->down();

    expect(Schema::hasTable('requests'))->toBeTrue()
        ->and(Schema::hasColumn('requests', 'user_id'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'role'))->toBeFalse()
        ->and(DB::table('requests')->where('id', $requestId)->value('item_name'))
        ->toBe('Existing lab equipment');

    $migration->up();

    expect(Schema::hasColumn('requests', 'user_id'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'role'))->toBeTrue()
        ->and(DB::table('requests')->where('id', $requestId)->value('item_name'))
        ->toBe('Existing lab equipment');
});

test('students and administrators can sign in and sign out', function () {
    $this->seed(DatabaseSeeder::class);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Sign in to your workspace');

    $this->post(route('login'), [
        'email' => 'avery.student@example.test',
        'password' => 'student-demo-password',
    ])->assertRedirect(route('tasks.index'));

    $this->assertAuthenticatedAs(User::query()->where('email', 'avery.student@example.test')->firstOrFail());

    $this->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $this->post(route('login'), [
        'email' => 'jordan.student@example.test',
        'password' => 'student-demo-password',
    ])->assertRedirect(route('tasks.index'));

    $this->assertAuthenticatedAs(User::query()->where('email', 'jordan.student@example.test')->firstOrFail());

    $this->post(route('logout'));
    $this->assertGuest();

    $this->post(route('login'), [
        'email' => 'morgan.admin@example.test',
        'password' => 'admin-demo-password',
    ])->assertRedirect(route('tasks.index'));

    $this->assertAuthenticatedAs(User::query()->where('email', 'morgan.admin@example.test')->firstOrFail());
});

test('invalid credentials do not authenticate a user', function () {
    User::factory()->create([
        'email' => 'avery.student@example.test',
        'password' => 'correct-password',
    ]);

    $this->from(route('login'))
        ->post(route('login'), [
            'email' => 'avery.student@example.test',
            'password' => 'incorrect-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
