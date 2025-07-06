<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AuthorizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'crud-item']);
        Permission::create(['name' => 'view-item']);

        $superadminRole = Role::create(['name' => 'superadmin']);

        $warehouseWorker = Role::create(['name' => 'warehouse_worker']);
        $warehouseWorker->givePermissionTo('view-item');

        $warehouseWorkerUser = User::factory()->create([
            'name' => 'Pekerja Gudang',
            'email' => 'kerja@truckflex.com',
            'password' => '12345678'
        ])->assignRole($warehouseWorker);

        $superadminUser = User::factory()->create([
            'name' => 'Superadmin',
            'email' => 'superadmin@truckflex.com',
            'password' => '12345678'
        ])->assignRole($superadminRole);
    }
}
