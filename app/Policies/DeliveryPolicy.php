<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DeliveryPolicy
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
        
        // Superadmins can view all deliveries
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse managers can view all deliveries related to their warehouse
        if ($user->hasRole('warehouse_manager')) {
            return $user->hasPermissionTo('view-all-stock');
        }
        
        // Drivers can only see their assigned deliveries
        if ($user->hasRole('driver')) {
            return $user->hasPermissionTo('view-assigned-deliveries');
        }
        
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Delivery $delivery): bool
    {
        if (!$user) {
            return false;
        }

        // Using typecasting for clarity when accessing user methods
        /** @var \App\Models\User $user */
        
        // Superadmin can view any delivery
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Driver can only view deliveries assigned to them
        if ($user->hasRole('driver')) {
            return $delivery->driver_id === $user->id;
        }
        
        // Warehouse manager can only view deliveries related to their warehouse
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('view-deliveries')) {
            return $delivery->from_warehouse_id === $user->warehouse_id || 
                   $delivery->to_warehouse_id === $user->warehouse_id;
        }
        
        return $user->hasPermissionTo('view-all-stock');
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
        
        // Superadmin can create any delivery
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager with manage-deliveries permission can create deliveries
        if ($user->hasRole('warehouse_manager')) {
            return $user->hasPermissionTo('manage-deliveries');
        }
        
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Delivery $delivery): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Superadmin can update any delivery
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Driver cannot update deliveries
        if ($user->hasRole('driver')) {
            return false;
        }
        
        // Warehouse manager can only update deliveries related to their warehouse
        if ($user->hasRole('warehouse_manager')) {
            return ($delivery->from_warehouse_id === $user->warehouse_id || 
                   $delivery->to_warehouse_id === $user->warehouse_id) && 
                   $user->hasPermissionTo('manage-deliveries');
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Delivery $delivery): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Only superadmin can delete any delivery
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager can only delete deliveries related to their warehouse
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('manage-deliveries')) {
            return $delivery->from_warehouse_id === $user->warehouse_id || 
                   $delivery->to_warehouse_id === $user->warehouse_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Delivery $delivery): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Only superadmin can restore any delivery
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager can only restore deliveries related to their warehouse
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('manage-deliveries')) {
            return $delivery->from_warehouse_id === $user->warehouse_id || 
                   $delivery->to_warehouse_id === $user->warehouse_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Delivery $delivery): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Only superadmin can force delete
        return $user->hasRole('superadmin');
    }
}
