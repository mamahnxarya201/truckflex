<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Superadmin can view all items
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        return $user->hasPermissionTo('view-item');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Item $item): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Superadmin can view any item
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        return $user->hasPermissionTo('view-item');
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
        
        // Superadmin can create items
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        return $user->hasPermissionTo('crud-item');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Item $item): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Superadmin can update any item
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager can update items with proper permission
        if ($user->hasRole('warehouse_manager')) {
            return $user->hasPermissionTo('crud-item');
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Item $item): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Superadmin can delete any item
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        return $user->hasPermissionTo('crud-item');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Item $item): bool
    {
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        
        // Superadmin can restore any item
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        return $user->hasPermissionTo('crud-item');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Item $item): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmin can permanently delete items
        return $user->hasRole('superadmin');
    }
}
