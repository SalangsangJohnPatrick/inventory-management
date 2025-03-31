<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory; // Import Inventory model
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Generate 20 fake inventory items
        for ($i = 0; $i < 20; $i++) {
            Inventory::create([
                'brand_name' => $faker->company,
                'type' => $faker->randomElement(['Laptop', 'Monitor', 'Keyboard', 'Mouse', 'Printer']),
                'quantity_on_hand' => $faker->numberBetween(5, 100),
                'price' => $faker->randomFloat(2, 500, 50000), // Price between 500 and 50,000
                'products_sold' => $faker->numberBetween(0, 50),
            ]);
        }
    }
}
