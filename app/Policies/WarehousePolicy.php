<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Auth\Access\Response;

class WarehousePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Anyone with view-item permission can view warehouses
        return $user->hasPermissionTo('view-item') || 
               $user->hasPermissionTo('view-all-warehouses') || 
               $user->hasPermissionTo('manage-warehouse');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        // Superadmin can view any warehouse
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse admin can only view their assigned warehouse
        if ($user->isWarehouseAdmin()) {
            return $user->warehouse_id === $warehouse->id;
        }
        
        return $user->hasPermissionTo('view-all-warehouses');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only superadmin can create warehouses
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        // Superadmin can update any warehouse
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse admin can only update their assigned warehouse
        if ($user->isWarehouseAdmin()) {
            return $user->warehouse_id === $warehouse->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        // Only superadmin can delete warehouses
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Warehouse $warehouse): bool
    {
        // Only superadmin can restore warehouses
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Warehouse $warehouse): bool
    {
        // Only superadmin can force delete warehouses
        return $user->hasRole('superadmin');
    }
}
