<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InventoryMovementDirection;
use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function entry(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        ?User $user = null,
        ?string $notes = null,
    ): void
    {
        $this->createMovement(
            $product,
            $warehouse,
            InventoryMovementType::Purchase,
            InventoryMovementDirection::In,
            $quantity,
            $user,
            $notes,
        );
    }

    public function exit(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        ?User $user = null,
        ?string $notes = null,
    ): void
    {
        $this->createMovement(
            $product,
            $warehouse,
            InventoryMovementType::Sale,
            InventoryMovementDirection::Out,
            $quantity,
            $user,
            $notes,
        );
    }

    public function adjust(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        InventoryMovementDirection $direction,
        ?User $user = null,
        ?string $notes = null,
    ): void
    {
        $this->createMovement(
            $product,
            $warehouse,
            InventoryMovementType::Adjustment,
            $direction,
            $quantity,
            $user,
            $notes,
        );
    }

    public function transfer(
        Product $product,
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        float $quantity,
        ?User $user = null,
        ?string $notes = null,
    ): void
    {
        DB::transaction(function () use (
            $product,
            $fromWarehouse,
            $toWarehouse,
            $quantity,
            $user,
            $notes,
        ) {

            $this->createMovement(
                $product,
                $fromWarehouse,
                InventoryMovementType::Transfer,
                InventoryMovementDirection::Out,
                $quantity,
                $user,
                $notes,
            );

            $this->createMovement(
                $product,
                $toWarehouse,
                InventoryMovementType::Transfer,
                InventoryMovementDirection::In,
                $quantity,
                $user,
                $notes,
            );

        });
    }

    private function createMovement(
        Product $product,
        Warehouse $warehouse,
        InventoryMovementType $type,
        InventoryMovementDirection $direction,
        float $quantity,
        ?User $user,
        ?string $notes,)
    {
        InventoryMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => $type->value,
            'direction' => $direction->value,
            'quantity' => $quantity,
            'user_id' => $user?->id,
            'notes' => $notes,
        ]);
    }
}
