<?php

namespace App\Policies;

use App\Models\LedgerFactual;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LedgerFactualPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Anyone with view-all-stock or manage-incoming/outgoing permissions can view factual ledgers
        return $user->hasPermissionTo('view-all-stock') || 
               $user->hasPermissionTo('manage-incoming') || 
               $user->hasPermissionTo('manage-outgoing');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LedgerFactual $ledgerFactual): bool
    {
        // Superadmin can view any ledger entry
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse admin can only view entries related to their warehouse
        if ($user->isWarehouseAdmin()) {
            // Check if racks are in user's warehouse
            $fromRackInWarehouse = false;
            $toRackInWarehouse = false;
            
            if ($ledgerFactual->from_rack_id) {
                $fromRackInWarehouse = $ledgerFactual->fromRack && 
                                       $ledgerFactual->fromRack->warehouse_id === $user->warehouse_id;
            }
            
            if ($ledgerFactual->to_rack_id) {
                $toRackInWarehouse = $ledgerFactual->toRack && 
                                     $ledgerFactual->toRack->warehouse_id === $user->warehouse_id;
            }
            
            return $fromRackInWarehouse || $toRackInWarehouse;
        }
        
        return $user->hasPermissionTo('view-all-stock');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Superadmin or users with manage-incoming/outgoing permissions can create ledger entries
        return $user->hasRole('superadmin') ||
               $user->hasPermissionTo('manage-incoming') ||
               $user->hasPermissionTo('manage-outgoing');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LedgerFactual $ledgerFactual): bool
    {
        // Superadmin can update any ledger entry
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse admin can only update entries related to their warehouse
        if ($user->isWarehouseAdmin()) {
            // Check if racks are in user's warehouse
            $fromRackInWarehouse = false;
            $toRackInWarehouse = false;
            
            if ($ledgerFactual->from_rack_id) {
                $fromRackInWarehouse = $ledgerFactual->fromRack && 
                                       $ledgerFactual->fromRack->warehouse_id === $user->warehouse_id;
            }
            
            if ($ledgerFactual->to_rack_id) {
                $toRackInWarehouse = $ledgerFactual->toRack && 
                                     $ledgerFactual->toRack->warehouse_id === $user->warehouse_id;
            }
            
            return ($fromRackInWarehouse || $toRackInWarehouse) &&
                   $user->hasPermissionTo('manage-incoming') &&
                   $user->hasPermissionTo('manage-outgoing');
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LedgerFactual $ledgerFactual): bool
    {
        // Only superadmin can delete ledger entries
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LedgerFactual $ledgerFactual): bool
    {
        // Only superadmin can restore ledger entries
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LedgerFactual $ledgerFactual): bool
    {
        // Only superadmin can force delete ledger entries
        return $user->hasRole('superadmin');
    }
}
