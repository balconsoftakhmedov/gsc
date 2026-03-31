<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Domain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => env('ADMIN_EMAIL', 'admin@example.com'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
        ]);

        // Domains
        Domain::create([
            'name' => 'Altaudit',
            'slug' => 'altaudit',
            'site_url' => 'https://altaudit.com',
            'gsc_property' => 'sc-domain:altaudit.com',
            'is_active' => true,
        ]);

        Domain::create([
            'name' => 'Aiagentivo',
            'slug' => 'aiagentivo',
            'site_url' => 'https://aiagentivo.com',
            'gsc_property' => 'sc-domain:aiagentivo.com',
            'is_active' => true,
        ]);
    }
}
