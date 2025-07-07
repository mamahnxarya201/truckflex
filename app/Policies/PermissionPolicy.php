<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
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
        
        // Only superadmins can view permissions list
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Permission $permission): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmins can view permissions
        return $user->hasRole('superadmin');
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
        
        // Only superadmins can create permissions
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Permission $permission): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmins can update permissions
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Permission $permission): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmins can delete permissions, but let's prevent deleting essential permissions
        $essentialPermissions = [
            'view-item', 'crud-item', 'view-deliveries', 'manage-deliveries', 
            'view-assigned-deliveries', 'view-vehicles', 'manage-vehicles',
            'view-all-warehouses', 'manage-warehouse'
        ];
        
        return $user->hasRole('superadmin') && !in_array($permission->name, $essentialPermissions);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Permission $permission): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmins can restore permissions
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Permission $permission): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmins can force delete permissions, but let's prevent deleting essential permissions
        $essentialPermissions = [
            'view-item', 'crud-item', 'view-deliveries', 'manage-deliveries', 
            'view-assigned-deliveries', 'view-vehicles', 'manage-vehicles',
            'view-all-warehouses', 'manage-warehouse'
        ];
        
        return $user->hasRole('superadmin') && !in_array($permission->name, $essentialPermissions);
    }
}
