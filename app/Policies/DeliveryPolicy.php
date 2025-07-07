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
    public function viewAny(User $user): bool
    {
        // Anyone with view-all-stock or manage-deliveries permission can view deliveries
        return $user->hasPermissionTo('view-all-stock') || 
               $user->hasPermissionTo('manage-deliveries');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Delivery $delivery): bool
    {
        // Superadmin can view any delivery
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse admin can only view deliveries related to their warehouse
        if ($user->hasRole('warehouse_admin')) {
            return $delivery->from_warehouse_id === $user->warehouse_id || 
                   $delivery->to_warehouse_id === $user->warehouse_id;
        }
        
        return $user->hasPermissionTo('view-all-stock');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Superadmin or users with manage-deliveries permission can create deliveries
        return $user->hasRole('superadmin') || $user->hasPermissionTo('manage-deliveries');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Delivery $delivery): bool
    {
        // Superadmin can update any delivery
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse admin can only update deliveries related to their warehouse
        if ($user->hasRole('warehouse_admin')) {
            $isRelatedToWarehouse = $delivery->from_warehouse_id === $user->warehouse_id || 
                                   $delivery->to_warehouse_id === $user->warehouse_id;
                                   
            return $isRelatedToWarehouse && $user->hasPermissionTo('manage-deliveries');
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Delivery $delivery): bool
    {
        // Only superadmin can delete deliveries
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Delivery $delivery): bool
    {
        // Only superadmin can restore deliveries
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Delivery $delivery): bool
    {
        // Only superadmin can force delete deliveries
        return $user->hasRole('superadmin');
    }
}
