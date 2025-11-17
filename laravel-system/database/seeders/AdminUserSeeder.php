<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admin_users')->insert([
            'username' => 'admin',
            'password_hash' => Hash::make('admin123'),
            'email' => 'admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
