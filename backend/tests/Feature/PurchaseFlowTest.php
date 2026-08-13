<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Compras\PurchaseStatus;
use App\Enums\Finanzas\AccountPayableStatus;
use App\Models\Company;
use App\Models\Compras\Supplier;
use App\Models\Compras\Tax;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_confirm_purchase(): void
    {
        $company = Company::create([
            'name' => 'Empresa Test',
            'legal_name' => 'Empresa Test S.A.C.',
            'tax_id' => '20123456789',
            'email' => 'test@example.com',
            'currency' => 'PEN',
            'status' => 'active',
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Usuario Test',
            'email' => 'user@test.com',
            'password' => 'password',
        ]);

        Sanctum::actingAs($user);

        $supplier = Supplier::create([
            'company_id' => $company->id,
            'name' => 'Proveedor Test',
            'legal_name' => 'Proveedor Test S.A.C.',
            'tax_id' => '20111111111',
            'email' => 'proveedor@test.com',
            'phone' => '999999999',
            'address' => 'Av. Test 123',
            'is_active' => true,
        ]);

        $tax = Tax::create([
            'company_id' => $company->id,
            'name' => 'IGV',
            'code' => 'IGV',
            'rate' => 18,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::create([
            'company_id' => $company->id,
            'name' => 'Almacén Test',
            'code' => 'ALM-TEST',
            'address' => 'Av. Test 123',
            'is_active' => true,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'name' => 'Producto Test',
            'sku' => 'TEST-001',
            'barcode' => '775000000001',
            'cost' => 10,
            'price' => 15,
            'stock_min' => 5,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/purchases', [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'purchase_date' => '2026-08-13',
            'reference' => 'TEST-PURCHASE-001',
            'notes' => 'Compra creada desde Feature Test',
            'items' => [
                [
                    'product_id' => $product->id,
                    'tax_id' => $tax->id,
                    'quantity' => 10,
                    'unit_cost' => 10,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.reference', 'TEST-PURCHASE-001')
            ->assertJsonPath(
                'data.status',
                PurchaseStatus::DRAFT->value
            )
            ->assertJsonPath('data.subtotal', 100)
            ->assertJsonPath('data.tax', 18)
            ->assertJsonPath('data.total', 118);

        $purchaseId = $response->json('data.id');

        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'company_id' => $company->id,
            'status' => PurchaseStatus::DRAFT->value,
            'total' => 118.00,
        ]);

        $confirmResponse = $this->postJson(
            "/api/purchases/{$purchaseId}/confirm"
        );

        $confirmResponse
            ->assertOk()
            ->assertJsonPath(
                'data.status',
                PurchaseStatus::CONFIRMED->value
            );

        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'company_id' => $company->id,
            'status' => PurchaseStatus::CONFIRMED->value,
        ]);

        $this->assertDatabaseHas('account_payables', [
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'purchase_id' => $purchaseId,
            'amount' => 118.00,
            'paid_amount' => 0,
            'balance' => 118.00,
            'status' => AccountPayableStatus::PENDING->value,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'company_id' => $company->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'purchase',
            'direction' => 'in',
            'quantity' => 10.000,
            'reference_id' => $purchaseId,
        ]);
    }
}
