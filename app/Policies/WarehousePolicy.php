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
    public function viewAny(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        // Using typecasting for clarity when accessing user methods
        /** @var \App\Models\User $user */

        // Superadmin can view all warehouses
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager can view all warehouses
        if ($user->hasRole('warehouse_manager')) {
            return $user->hasPermissionTo('view-all-warehouses');
        }
        
        // Any user with these permissions can view warehouses
        return $user->hasPermissionTo('view-item') || 
               $user->hasPermissionTo('view-all-warehouses') || 
               $user->hasPermissionTo('manage-warehouse');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Warehouse $warehouse): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Superadmin can view any warehouse
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager can view all warehouses but primarily their assigned warehouse
        if ($user->hasRole('warehouse_manager')) {
            return $user->hasPermissionTo('view-all-warehouses');
        }
        
        return $user->hasPermissionTo('view-all-warehouses');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Only superadmin can create warehouses
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Warehouse $warehouse): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Superadmin can update any warehouse
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager can only update their assigned warehouse
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('manage-warehouse')) {
            return $user->warehouse_id === $warehouse->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Warehouse $warehouse): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */

        // Only superadmin can delete warehouses
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Warehouse $warehouse): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */

        // Only superadmin can restore warehouses
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Warehouse $warehouse): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */

        // Only superadmin can force delete warehouses
        return $user->hasRole('superadmin');
    }
}
