<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('Password123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Official Test',
                'email' => 'official@official.com',
                'password' => Hash::make('Password123'),
                'role' => 'official',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Official Test 2',
                'email' => 'official2@official.com',
                'password' => Hash::make('Password123'),
                'role' => 'official',
                'created_at' => now(),
                'updated_at' => now(),
            ],
                        [
                'name' => 'Official Test 3',
                'email' => 'official3@official.com',
                'password' => Hash::make('Password123'),
                'role' => 'official',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Captain',
                'email' => 'captain@captain.com',
                'password' => Hash::make('Password123'),
                'role' => 'captain',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Secretary',
                'email' => 'secretary@secretary.com',
                'password' => Hash::make('Password123'),
                'role' => 'secretary',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Treasurer',
                'email' => 'treasurer@treasurer.com',
                'password' => Hash::make('Password123'),
                'role' => 'treasurer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}