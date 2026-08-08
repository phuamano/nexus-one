<?php
declare(strict_types=1);

namespace App\Models\Compras;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id','name', 'code','rate', 'is_active'])]
class Tax extends TenantModel
{
    //
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
