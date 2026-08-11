<?php
declare(strict_types=1);

namespace App\Models\Compras;

use App\Enums\Compras\PurchaseStatus;
use App\Models\InventoryMovement;
use App\Models\TenantModel;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['company_id','supplier_id', 'warehouse_id','user_id', 'purchase_date','reference','status','subtotal',
    'tax','total','notes'])]
class Purchase extends TenantModel
{
    //

    protected function casts(): array
    {
        return [
            'purchase_date' => 'datetime',
            'status' => PurchaseStatus::class,
        ];
    }
    public function supplier(): BelongsTo{
        return $this->belongsTo(Supplier::class);
    }
    public function warehouse(): BelongsTo{
        return $this->belongsTo(Warehouse::class);
    }
    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
    public function purchaseItems(): HasMany{
        return $this->hasMany(PurchaseItem::class);
    }
    public function inventoryMovements(): MorphMany
    {
        return $this->morphMany(
            InventoryMovement::class,
            'reference'
        );
    }
}
