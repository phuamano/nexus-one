<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;

abstract class BaseAuthenticatable extends Authenticatable
{
    use HasUuids;
}
