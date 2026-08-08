<?php

declare(strict_types=1);

namespace App\Services\Compras;

use App\Enums\Compras\PurchaseStatus;
use App\Models\Compras\Purchase;
use App\Models\Compras\Tax;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function create(array $data): Purchase
    {
        return DB::transaction(function () use ($data): Purchase {
            $items = $data['items'];

            unset($data['items']);

            $purchase = Purchase::create([
                ...$data,
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
            ]);

            foreach ($items as $item) {
                $this->createItem($purchase, $item);
            }

            $this->recalculateTotals($purchase);

            return $purchase->fresh('purchaseItems');
        });
    }

    private function createItem(Purchase $purchase, array $item): void
    {
        $tax = Tax::findOrFail($item['tax_id']);

        $quantity = (float) $item['quantity'];
        $unitCost = (float) $item['unit_cost'];

        $subtotal = $quantity * $unitCost;
        $taxAmount = $subtotal * ((float) $tax->rate / 100);
        $total = $subtotal + $taxAmount;

        $purchase->purchaseItems()->create([
            'product_id' => $item['product_id'],
            'tax_id' => $tax->id,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'tax_amount' => $taxAmount,
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }

    private function recalculateTotals(Purchase $purchase): void
    {
        $subtotal = $purchase->purchaseItems->sum('subtotal');
        $tax = $purchase->purchaseItems->sum('tax_amount');

        $purchase->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data): Purchase {
            if ($purchase->status !== PurchaseStatus::DRAFT){
                throw new \DomainException(
                    'Solo se pueden modificar compras en estado draft.'
                );
            }

            $items = $data['items'];

            unset($data['items']);

            $purchase->update($data);

            $purchase->purchaseItems()->delete();

            foreach ($items as $item) {
                $this->createItem($purchase, $item);
            }

            $this->recalculateTotals($purchase);

            return $purchase->fresh('purchaseItems');
        });
    }

    public function confirm(Purchase $purchase): Purchase
    {
        if ($purchase->status !== PurchaseStatus::DRAFT) {
            throw new \DomainException(
                'Solo se pueden confirmar compras en estado draft.'
            );
        }

        $purchase->update([
            'status' => 'confirmed',
        ]);

        return $purchase->fresh('purchaseItems');
    }
}
