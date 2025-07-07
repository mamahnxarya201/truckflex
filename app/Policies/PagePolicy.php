<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class PagePolicy
{
    /**
     * Determine whether the user can view the stock viewer page.
     */
    public function viewStockViewer(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Deny access to drivers
        if ($user->hasRole('driver')) {
            return false;
        }
        
        // Allow access to superadmin and warehouse_manager
        return $user->hasRole('superadmin') || $user->hasRole('warehouse_manager');
    }
    
    /**
     * Determine whether the user can access the system management group.
     */
    public function accessSystemManagement(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        /** @var \App\Models\User $user */
        
        // Only superadmin can access system management
        return $user->hasRole('superadmin');
    }
}
