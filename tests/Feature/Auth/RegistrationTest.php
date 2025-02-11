<?php

declare(strict_types=1);

use App\Models\User;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function(){
    $this->userValidate = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];
});

test('registration screen can be rendered', function () {
    $response = get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = post('/register', $this->userValidate);

    assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('a wallet is created for newly registered users', function(){
    $response = post('/register', $this->userValidate);

    $response->assertRedirect(route('dashboard', absolute: false));
    $user = User::where('email', $this->userValidate['email'])->first();
    expect($user->wallet)->not->toBeNull();
});
