<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Role;

class RolePolicy
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
        
        // Only superadmins and warehouse_managers can view roles list
        return $user->hasRole('superadmin') || $user->hasRole('warehouse_manager');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Role $role): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmins and warehouse_managers can view roles
        return $user->hasRole('superadmin') || $user->hasRole('warehouse_manager');
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
        
        // Only superadmins can create roles
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Role $role): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmins can update roles
        // Also prevent updating the superadmin role
        return $user->hasRole('superadmin') && $role->name !== 'superadmin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Role $role): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmins can delete roles
        // Prevent deleting the superadmin, warehouse_manager, driver roles
        return $user->hasRole('superadmin') && 
               !in_array($role->name, ['superadmin', 'warehouse_manager', 'driver']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Role $role): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmins can restore roles
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Role $role): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmins can force delete roles
        // Prevent deleting the superadmin, warehouse_manager, driver roles
        return $user->hasRole('superadmin') && 
               !in_array($role->name, ['superadmin', 'warehouse_manager', 'driver']);
    }
}
