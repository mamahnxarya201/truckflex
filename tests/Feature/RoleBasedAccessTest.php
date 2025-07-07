<?php

use App\Models\Delivery;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function() {
    // Clear permission cache
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    // Create test permissions if they don't exist
    Permission::firstOrCreate(['name' => 'view-item']);
    Permission::firstOrCreate(['name' => 'view-deliveries']);
    Permission::firstOrCreate(['name' => 'manage-deliveries']);
    Permission::firstOrCreate(['name' => 'view-assigned-deliveries']);
    Permission::firstOrCreate(['name' => 'view-vehicles']);
    Permission::firstOrCreate(['name' => 'manage-vehicles']);
    Permission::firstOrCreate(['name' => 'view-all-warehouses']);
    Permission::firstOrCreate(['name' => 'manage-warehouse']);
    Permission::firstOrCreate(['name' => 'crud-item']);
    Permission::firstOrCreate(['name' => 'view-all-stock']);
    Permission::firstOrCreate(['name' => 'manage-incoming']);
    Permission::firstOrCreate(['name' => 'manage-outgoing']);
    Permission::firstOrCreate(['name' => 'view-reports']);
    
    // Create roles or get existing ones
    $this->superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
    $this->superadminRole->syncPermissions(Permission::all());
    
    $this->warehouseManagerRole = Role::firstOrCreate(['name' => 'warehouse_manager']);
    $this->warehouseManagerRole->syncPermissions([
        'view-item',
        'crud-item',
        'view-deliveries',
        'manage-deliveries',
        'view-vehicles',
        'manage-vehicles',
        'view-all-warehouses',
        'manage-warehouse',
        'view-all-stock'
    ]);
    
    $this->driverRole = Role::firstOrCreate(['name' => 'driver']);
    $this->driverRole->syncPermissions([
        'view-assigned-deliveries',
        'view-vehicles'
    ]);
    
    // Create a test warehouse
    $this->warehouse = Warehouse::factory()->create();
    
    // Create test users
    $this->superadmin = User::factory()->create(['name' => 'Test Superadmin']);
    $this->superadmin->assignRole('superadmin');
    
    $this->warehouseManager = User::factory()->create([
        'name' => 'Test Warehouse Manager',
        'warehouse_id' => $this->warehouse->id
    ]);
    $this->warehouseManager->assignRole('warehouse_manager');
    
    $this->driver = User::factory()->create(['name' => 'Test Driver']);
    $this->driver->assignRole('driver');
    
    // Create test resources
    $this->delivery = Delivery::factory()->create([
        'from_warehouse_id' => $this->warehouse->id,
        'driver_id' => $this->driver->id
    ]);
    
    $this->vehicle = Vehicle::factory()->create();
});

test('superadmin can view all resources', function() {
    actingAs($this->superadmin);
    
    // Test policy authorizations directly
    expect($this->superadmin->can('viewAny', Delivery::class))->toBeTrue();
    expect($this->superadmin->can('viewAny', Vehicle::class))->toBeTrue();
    expect($this->superadmin->can('viewAny', Warehouse::class))->toBeTrue();
    
    expect($this->superadmin->can('view', $this->delivery))->toBeTrue();
    expect($this->superadmin->can('view', $this->vehicle))->toBeTrue();
    expect($this->superadmin->can('view', $this->warehouse))->toBeTrue();
    
    // Test creation permissions
    expect($this->superadmin->can('create', Delivery::class))->toBeTrue();
    expect($this->superadmin->can('create', Vehicle::class))->toBeTrue();
    expect($this->superadmin->can('create', Warehouse::class))->toBeTrue();
    
    // Test update permissions
    expect($this->superadmin->can('update', $this->delivery))->toBeTrue();
    expect($this->superadmin->can('update', $this->vehicle))->toBeTrue();
    expect($this->superadmin->can('update', $this->warehouse))->toBeTrue();
    
    // Test delete permissions
    expect($this->superadmin->can('delete', $this->delivery))->toBeTrue();
    expect($this->superadmin->can('delete', $this->vehicle))->toBeTrue();
    expect($this->superadmin->can('delete', $this->warehouse))->toBeTrue();
});

test('warehouse manager can manage resources related to their warehouse', function() {
    actingAs($this->warehouseManager);
    
    // Should have view permission for all resources
    expect($this->warehouseManager->can('viewAny', Delivery::class))->toBeTrue();
    expect($this->warehouseManager->can('viewAny', Vehicle::class))->toBeTrue();
    expect($this->warehouseManager->can('viewAny', Warehouse::class))->toBeTrue();
    
    // Should be able to view specific resources
    expect($this->warehouseManager->can('view', $this->delivery))->toBeTrue(); // Related to their warehouse
    expect($this->warehouseManager->can('view', $this->vehicle))->toBeTrue();
    expect($this->warehouseManager->can('view', $this->warehouse))->toBeTrue();
    
    // Should be able to manage deliveries related to their warehouse
    expect($this->warehouseManager->can('update', $this->delivery))->toBeTrue();
    expect($this->warehouseManager->can('create', Delivery::class))->toBeTrue();
    
    // Should be able to manage vehicles
    expect($this->warehouseManager->can('update', $this->vehicle))->toBeTrue();
    expect($this->warehouseManager->can('create', Vehicle::class))->toBeTrue();
    
    // Should NOT be able to create warehouses
    expect($this->warehouseManager->can('create', Warehouse::class))->toBeFalse();
    
    // Should be able to update only their own warehouse
    expect($this->warehouseManager->can('update', $this->warehouse))->toBeTrue();
    
    // Create another warehouse with a guaranteed unique code
    $uniqueCode = 'GD-TEST-' . uniqid();
    $otherWarehouse = Warehouse::factory()->create([
        'code' => $uniqueCode
    ]);
    
    expect($this->warehouseManager->can('update', $otherWarehouse))->toBeFalse();
});

test('driver can only view their assigned deliveries and vehicles', function() {
    actingAs($this->driver);
    
    // Should be able to view deliveries (filtered to assigned ones at query level)
    expect($this->driver->can('viewAny', Delivery::class))->toBeTrue();
    
    // Should be able to view their assigned delivery
    expect($this->driver->can('view', $this->delivery))->toBeTrue();
    
    // Should be able to view vehicles
    expect($this->driver->can('viewAny', Vehicle::class))->toBeTrue();
    expect($this->driver->can('view', $this->vehicle))->toBeTrue();
    
    // Should NOT be able to manage any resources
    expect($this->driver->can('create', Delivery::class))->toBeFalse();
    expect($this->driver->can('update', $this->delivery))->toBeFalse();
    expect($this->driver->can('delete', $this->delivery))->toBeFalse();
    
    expect($this->driver->can('create', Vehicle::class))->toBeFalse();
    expect($this->driver->can('update', $this->vehicle))->toBeFalse();
    
    // Should have limited warehouse access
    expect($this->driver->can('viewAny', Warehouse::class))->toBeFalse();
    expect($this->driver->can('view', $this->warehouse))->toBeFalse();
});
