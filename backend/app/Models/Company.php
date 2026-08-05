<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'legal_name',
    'tax_id',
    'email',
    'phone',
    'website',
    'timezone',
    'locale',
    'currency',
    'status',
])]

class Company extends BaseModel
{
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
