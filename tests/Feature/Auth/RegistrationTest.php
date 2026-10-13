<?php

test('public registration is disabled', function () {
    $this->get('/register')->assertNotFound();
});

test('public registration submissions are rejected', function () {
    $this->post('/register', [
        'nama' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    $this->assertGuest();
});
