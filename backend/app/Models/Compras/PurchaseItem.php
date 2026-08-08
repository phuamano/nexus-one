<?php
declare(strict_types=1);

namespace App\Models\Compras;

use App\Models\Product;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['purchase_id','product_id', 'tax_id', 'quantity','unit_cost','tax_amount','subtotal','total'])]
class PurchaseItem extends TenantModel
{
    //
    public function purchase(): BelongsTo{
        return $this->belongsTo(Purchase::class);
    }
    public function product(): BelongsTo{
        return $this->belongsTo(Product::class);
    }
    public function tax(): BelongsTo{
        return $this->belongsTo(Tax::class);
    }
}
