<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Compras\PurchaseStatus;
use App\Models\Compras\Purchase;
use App\Models\Compras\PurchaseItem;
use App\Models\Compras\Supplier;
use App\Models\Compras\Tax;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $supplier = Supplier::where('tax_id', '20123456789')->firstOrFail();

        $warehouse = Warehouse::firstOrFail();

        $user = User::firstOrFail();

        $product = Product::where('sku', 'PROD-001')->firstOrFail();

        $tax = Tax::where('code', 'IGV')->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Purchase data
        |--------------------------------------------------------------------------
        */

        $quantity = 10;
        $unitCost = 10.00;

        $subtotal = round($quantity * $unitCost, 2);

        $taxAmount = round(
            $subtotal * ((float) $tax->rate / 100),
            2
        );

        $total = round($subtotal + $taxAmount, 2);

        /*
        |--------------------------------------------------------------------------
        | Purchase
        |--------------------------------------------------------------------------
        */

        $purchase = Purchase::firstOrCreate(
            [
                'reference' => 'COMP-0001',
            ],
            [
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => $user->id,
                'purchase_date' => now(),
                'status' => PurchaseStatus::cases()[0],
                'subtotal' => $subtotal,
                'tax' => $taxAmount,
                'total' => $total,
                'notes' => 'Compra de prueba generada por el Seeder.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Purchase item
        |--------------------------------------------------------------------------
        */

        PurchaseItem::firstOrCreate(
            [
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
            ],
            [
                'tax_id' => $tax->id,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'tax_amount' => $taxAmount,
                'subtotal' => $subtotal,
                'total' => $total,
            ]
        );
    }
}
