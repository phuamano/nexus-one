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

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
}
