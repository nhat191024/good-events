<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'partner']);
        Role::create(['name' => 'client']);
        Role::create(['name' => 'human_resource_manager']);
        Role::create(['name' => 'design_manager']);
        Role::create(['name' => 'rental_manager']);
        Role::create(['name' => 'blog_manager']);
    }
}
