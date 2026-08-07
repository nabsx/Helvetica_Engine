<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Staff accounts (PIN is auto-hashed by User::setPinAttribute) ---
        User::create(['name' => 'Admin Helvetica', 'pin' => '123456', 'role' => 'admin']);
        User::create(['name' => 'Kasir Satu', 'pin' => '112233', 'role' => 'cashier']);

        // --- Categories ---
        $coffee = Category::create(['name' => 'Coffee', 'slug' => 'coffee']);
        $nonCoffee = Category::create(['name' => 'Non-Coffee', 'slug' => 'non-coffee']);
        $pastry = Category::create(['name' => 'Pastry', 'slug' => 'pastry']);

        // --- Menu items ---
        $now = now();

        Product::insert([
            ['category_id' => $coffee->id, 'name' => 'Helvetica Latte', 'price' => 18000, 'image' => null, 'is_available' => true, 'created_at' => $now, 'updated_at' => $now],
            ['category_id' => $coffee->id, 'name' => 'Espresso', 'price' => 15000, 'image' => null, 'is_available' => true, 'created_at' => $now, 'updated_at' => $now],
            ['category_id' => $nonCoffee->id, 'name' => 'Matcha Latte', 'price' => 20000, 'image' => null, 'is_available' => true, 'created_at' => $now, 'updated_at' => $now],
            ['category_id' => $nonCoffee->id, 'name' => 'Iced Lemon Tea', 'price' => 12000, 'image' => null, 'is_available' => true, 'created_at' => $now, 'updated_at' => $now],
            ['category_id' => $pastry->id, 'name' => 'Butter Cookie', 'price' => 12000, 'image' => null, 'is_available' => true, 'created_at' => $now, 'updated_at' => $now],
            ['category_id' => $pastry->id, 'name' => 'Croissant', 'price' => 15000, 'image' => null, 'is_available' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
