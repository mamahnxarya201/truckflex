<?php

namespace App\Policies;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class VehiclePolicy
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
        
        // Superadmins can view all vehicles
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse managers can view vehicles
        if ($user->hasRole('warehouse_manager')) {
            return $user->hasPermissionTo('view-vehicles');
        }
        
        // Drivers can view vehicles they drive
        if ($user->hasRole('driver')) {
            return $user->hasPermissionTo('view-vehicles');
        }
        
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Vehicle $vehicle): bool
    {
        if (!$user) {
            return false;
        }

        // Using typecasting for clarity when accessing user methods
        /** @var \App\Models\User $user */
        
        // Superadmin can view any vehicle
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager can view vehicles (should be further restricted by warehouse in the future)
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('view-vehicles')) {
            return true;
        }
        
        // Driver can view vehicles they've driven (should be restricted by logs in the future)
        if ($user->hasRole('driver') && $user->hasPermissionTo('view-vehicles')) {
            return true;
        }
        
        return false;
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
        
        // Only superadmin and warehouse managers can create vehicles
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('manage-vehicles')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Vehicle $vehicle): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Superadmin can update any vehicle
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Driver cannot update vehicles
        if ($user->hasRole('driver')) {
            return false;
        }
        
        // Warehouse manager can update vehicles with proper permission
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('manage-vehicles')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Vehicle $vehicle): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Only superadmin can delete vehicles
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager can delete vehicles with proper permission
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('manage-vehicles')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Vehicle $vehicle): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Only superadmin can restore vehicles
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager can restore vehicles with proper permission
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('manage-vehicles')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Vehicle $vehicle): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Only superadmin can force delete vehicles
        return $user->hasRole('superadmin');
    }
}
