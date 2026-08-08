<?php
declare(strict_types=1);

namespace App\Models\Compras;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id','name', 'legal_name','tax_id', 'email','phone','address', 'is_active'])]
class Supplier extends TenantModel
{
    //
    public function purchases(): HasMany{
        return $this->hasMany(Purchase::class);
    }
}
