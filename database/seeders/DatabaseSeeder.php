<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         User::factory()->create([
             'name' => 'John Doe',
             'email' => 'john@example.com',
             'password' => Hash::make('123456'),
         ]);

        User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('123456'),
        ]);

        User::factory()->create([
            'name' => 'John Smith',
            'email' => 'smith@example.com',
            'password' => Hash::make('123456'),
        ]);
    }
}
