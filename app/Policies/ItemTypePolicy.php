<?php

namespace App\Policies;

use App\Models\ItemType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ItemTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
       return $user->hasPermissionTo('view-item');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ItemType $item): bool
    {
        return $user->hasPermissionTo('view-item');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('crud-item');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ItemType $item): bool
    {
        return $user->hasPermissionTo('crud-item');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ItemType $item): bool
    {
        return $user->hasPermissionTo('crud-item');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ItemType $item): bool
    {
        return $user->hasPermissionTo('crud-item');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ItemType $item): bool
    {
        return $user->hasPermissionTo('crud-item');
    }
}
