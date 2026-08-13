<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Company & User
        |--------------------------------------------------------------------------
        */

        $this->call([
            CompanySeeder::class,
            UserSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Authenticate tenant user
        |--------------------------------------------------------------------------
        */

        $user = User::where('email', 'admin@test.com')->firstOrFail();

        Auth::login($user);

        /*
        |--------------------------------------------------------------------------
        | Tenant data
        |--------------------------------------------------------------------------
        */

        $this->call([
            PermissionSeeder::class,

            UnitSeeder::class,
            BrandSeeder::class,
            ProductCategorySeeder::class,
            WarehouseSeeder::class,
            ProductSeeder::class,

            TaxSeeder::class,
            SupplierSeeder::class,
            PurchaseSeeder::class,
            CustomerSeeder::class,
            FinancialAccountSeeder::class,
        ]);
    }
}
