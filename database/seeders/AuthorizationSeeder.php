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

        // Create permissions - using a more unified approach
        Permission::create(['name' => 'view-item']);
        Permission::create(['name' => 'crud-item']);
        Permission::create(['name' => 'view-all-warehouses']);
        Permission::create(['name' => 'manage-warehouse']);
        Permission::create(['name' => 'view-deliveries']);
        Permission::create(['name' => 'manage-deliveries']);
        Permission::create(['name' => 'view-assigned-deliveries']);
        Permission::create(['name' => 'view-vehicles']);
        Permission::create(['name' => 'manage-vehicles']);
        Permission::create(['name' => 'view-all-stock']);
        Permission::create(['name' => 'manage-incoming']);
        Permission::create(['name' => 'manage-outgoing']);
        Permission::create(['name' => 'view-reports']);
        Permission::create(['name' => 'create-vehicle-logs']);
        Permission::create(['name' => 'view-vehicle-types']);
        
        // Rack management permissions
        Permission::create(['name' => 'view-rack-blocks']);
        Permission::create(['name' => 'manage-rack-blocks']);
        Permission::create(['name' => 'view-rack-levels']);
        Permission::create(['name' => 'manage-rack-levels']);

        // Superadmin role - has all permissions
        $superadminRole = Role::create(['name' => 'superadmin']);
        $superadminRole->givePermissionTo([
            'view-item',
            'crud-item', 
            'view-all-warehouses', 
            'manage-warehouse', 
            'view-deliveries', 
            'manage-deliveries',
            'view-assigned-deliveries',
            'view-vehicles',
            'manage-vehicles',
            'view-all-stock',
            'manage-incoming',
            'manage-outgoing',
            'view-reports',
            'view-rack-blocks',
            'manage-rack-blocks',
            'view-rack-levels',
            'manage-rack-levels'
        ]);

        // Warehouse Manager role - can manage their assigned warehouse
        $warehouseManagerRole = Role::create(['name' => 'warehouse_manager']);
        $warehouseManagerRole->givePermissionTo([
            'view-item',
            'crud-item',
            'view-all-warehouses',    // Can view all warehouses
            'manage-warehouse',       // But can only manage their own (controlled by policy)
            'view-deliveries',        // Can view deliveries related to their warehouse
            'manage-deliveries',      // Can manage deliveries related to their warehouse
            'view-vehicles',          // Can view all vehicles
            'manage-vehicles',        // Can manage vehicles assigned to their warehouse
            'view-all-stock',         // Can view stock inventory
            'manage-incoming',        // Can manage incoming inventory
            'manage-outgoing',        // Can manage outgoing inventory
            'view-reports',            // Can view reports related to their warehouse
            'view-rack-blocks',       // Can view rack blocks
            'manage-rack-blocks',     // Can manage rack blocks
            'view-rack-levels',       // Can view rack levels
            'manage-rack-levels'      // Can manage rack levels
        ]);
        
        // Driver role - can only see assigned deliveries and vehicles
        $driverRole = Role::create(['name' => 'driver']);
        $driverRole->givePermissionTo([
            'view-assigned-deliveries',
            'view-vehicles',
            'view-reports',       // Limited reports for their deliveries
            'create-vehicle-logs' // Allow drivers to add vehicle logs
        ]);

        // Create users with our new unified role structure
        // Superadmin user
        $superadminUser = User::factory()->create([
            'name' => 'Superadmin',
            'email' => 'superadmin@truckflex.com',
            'password' => '12345678'
        ])->assignRole($superadminRole);

        // Warehouse Manager user
        $warehouseManagerUser = User::factory()->create([
            'name' => 'Manager Gudang',
            'email' => 'manager@truckflex.com',
            'password' => '12345678'
            // warehouse_id will be assigned in WarehouseSeeder
        ])->assignRole($warehouseManagerRole);

        $driverUser = User::factory()->create([
            'name' => 'Driver Satu',
            'email' => 'driver@truckflex.com',
            'password' => '12345678'
        ])->assignRole($driverRole);

        $driverUser2 = User::factory()->create([
            'name' => 'Driver Dua',
            'email' => 'driver2@truckflex.com',
            'password' => '12345678'
        ])->assignRole($driverRole);
    }
}
