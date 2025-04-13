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

        $brandNames = [
            'Logitech',
            'Razer',
            'SteelSeries',
            'Corsair',
            'HyperX',
            'Asus',
            'Microsoft',
            'Sony',
            'Turtle Beach',
            'Astro Gaming',
        ];

        for ($i = 0; $i < 50; $i++) {
            Inventory::create([
                'brand_name' => $faker->randomElement($brandNames),
                'type' => $faker->randomElement(['Laptop', 'Monitor', 'Keyboard', 'Mouse', 'Printer', 'Gaming Headset', 'Controller']),
                'quantity_on_hand' => $faker->numberBetween(5, 100),
                'price' => $faker->randomFloat(2, 500, 50000), // Price between 500 and 50,000
                'products_sold' => $faker->numberBetween(0, 50),
            ]);
        }
    }
}