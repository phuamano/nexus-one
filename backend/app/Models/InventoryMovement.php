<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends TenantModel
{
    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'type',
        'direction',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

}
