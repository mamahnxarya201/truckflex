<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
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

        // Create permissions
        Permission::create(['name' => 'crud-item']);
        Permission::create(['name' => 'view-item']);
        Permission::create(['name' => 'manage-warehouse']);
        Permission::create(['name' => 'view-all-warehouses']);
        Permission::create(['name' => 'manage-incoming']);
        Permission::create(['name' => 'manage-outgoing']);
        Permission::create(['name' => 'manage-deliveries']);
        Permission::create(['name' => 'view-all-stock']);

        // Superadmin role - has all permissions
        $superadminRole = Role::create(['name' => 'superadmin']);
        $superadminRole->givePermissionTo([
            'crud-item', 
            'view-item', 
            'manage-warehouse', 
            'view-all-warehouses', 
            'manage-incoming', 
            'manage-outgoing', 
            'manage-deliveries',
            'view-all-stock'
        ]);

        // Warehouse Worker role - limited permissions
        $warehouseWorker = Role::create(['name' => 'warehouse_worker']);
        $warehouseWorker->givePermissionTo(['view-item']);

        // Warehouse Admin role - can manage assigned warehouse
        $warehouseAdmin = Role::create(['name' => 'warehouse_admin']);
        $warehouseAdmin->givePermissionTo([
            'view-item',
            'manage-warehouse',
            'manage-incoming',
            'manage-outgoing',
        ]);

        // Create users
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

        $warehouseAdminUser = User::factory()->create([
            'name' => 'Admin Gudang',
            'email' => 'admin@truckflex.com',
            'password' => '12345678'
        ])->assignRole($warehouseAdmin);
        
        // Assign warehouse 1 to the warehouse admin if any warehouses exist
        if ($warehouse = Warehouse::first()) {
            $warehouseAdminUser->warehouse_id = $warehouse->id;
            $warehouseAdminUser->save();
        }

        User::factory()->create([
            'name' => 'Manager Pusat',
            'email' => 'manager@truckflex.com',
            'password' => '12345678'
        ]);

        User::factory()->create([
            'name' => 'Driver Satu',
            'email' => 'driver@truckflex.com',
            'password' => '12345678'
        ]);
    }
}
