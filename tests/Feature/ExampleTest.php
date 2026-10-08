<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to sign in before opening the workspace', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
