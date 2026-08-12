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
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;

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
        ?Model $reference = null,
    ): void {

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'La cantidad debe ser mayor que cero.',
            ]);
        }

        $this->createMovement(
            $product,
            $warehouse,
            InventoryMovementType::Purchase,
            InventoryMovementDirection::In,
            $quantity,
            $user,
            $notes,
            $reference,
        );
    }

    public function exit(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        ?User $user = null,
        ?string $notes = null,
        ?Model $reference = null,
    ): void {
        $available = $product->stockInWarehouse($warehouse);

        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => sprintf(
                    'Stock insuficiente para %s. Disponible: %s, solicitado: %s.',
                    $product->name,
                    $available,
                    $quantity
                ),
            ]);
        }

        $this->createMovement(
            $product,
            $warehouse,
            InventoryMovementType::Sale,
            InventoryMovementDirection::Out,
            $quantity,
            $user,
            $notes,
            $reference,
        );
    }

    public function adjust(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        InventoryMovementDirection $direction,
        ?User $user = null,
        ?string $notes = null,
    ): void {
        if ($direction === InventoryMovementDirection::Out) {
            $this->validateStock(
                $product,
                $warehouse,
                $quantity
            );
        }

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
    ): void {
        DB::transaction(function () use (
            $product,
            $fromWarehouse,
            $toWarehouse,
            $quantity,
            $user,
            $notes,
        ) {

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'La cantidad debe ser mayor que cero.',
                ]);
            }

            if ($fromWarehouse->id === $toWarehouse->id) {
                throw ValidationException::withMessages([
                    'warehouse' => 'El almacén de origen y destino deben ser diferentes.',
                ]);
            }

            $available = $product->stockInWarehouse($fromWarehouse);

            if ($available < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf(
                        'Stock insuficiente para %s en %s. Disponible: %s, solicitado: %s.',
                        $product->name,
                        $fromWarehouse->name,
                        $available,
                        $quantity
                    ),
                ]);
            }

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
        ?string $notes,
        ?Model $reference = null,
    ): void {
        InventoryMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => $type->value,
            'direction' => $direction->value,
            'quantity' => $quantity,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'user_id' => $user?->id,
            'notes' => $notes,
        ]);
    }

    private function validateStock(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
    ): void {
        $available = $product->stockInWarehouse($warehouse);

        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => sprintf(
                    'Stock insuficiente para %s. Disponible: %s, solicitado: %s.',
                    $product->name,
                    $available,
                    $quantity
                ),
            ]);
        }
    }

    public function return(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        ?User $user = null,
        ?string $notes = null,
        ?Model $reference = null,
    ): void {

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'La cantidad debe ser mayor que cero.',
            ]);
        }

        $this->createMovement(
            $product,
            $warehouse,
            InventoryMovementType::Return,
            InventoryMovementDirection::In,
            $quantity,
            $user,
            $notes,
            $reference,
        );
    }
}
