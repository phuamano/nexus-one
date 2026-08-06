<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CurrentCompany
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function user(): ?User
    {
        /** @var User|null */
        return Auth::user();
    }

    public function company(): ?Company
    {
        return $this->user()?->company;
    }

    public function id(): ?string
    {
        return $this->company()?->id;
    }
}
