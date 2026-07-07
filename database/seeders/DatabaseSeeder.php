<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@tulpar.de'],
            [
                'name'              => 'Admin',
                'password'          => bcrypt('tulpar'),
                'email_verified_at' => now(),
            ]
        );

        $this->call(ItemGroupSeeder::class);
    }
}
