<?php

use App\Models\User;

function devLogin(): User
{
    $user = User::firstOrFail();

    auth()->login($user);

    return $user;
}
