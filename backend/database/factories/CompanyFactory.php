<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'legal_name' => fake()->company() . ' S.A.C.',
            'tax_id' => fake()->unique()->numerify('20#########'),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('9########'),
            'website' => fake()->url(),
            'timezone' => 'America/Lima',
            'locale' => 'es-PE',
            'currency' => 'PEN',
            'status' => 'active',
        ];
    }
}
