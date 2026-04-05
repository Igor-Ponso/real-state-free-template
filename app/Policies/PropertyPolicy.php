<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

/**
 * Authorization policy for property CRUD operations.
 *
 * Admins have full access to all properties. Agents can manage
 * only their own listings. Clients have no management access.
 */
class PropertyPolicy
{
    /**
     * Admins and agents can view the property management index.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage-properties');
    }

    /**
     * Admins and agents can view any property detail in admin.
     */
    public function view(User $user, Property $property): bool
    {
        return $user->hasPermissionTo('manage-properties');
    }

    /**
     * Admins and agents can create new property listings.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-properties');
    }

    /**
     * Admins can update any property. Agents can only update their own.
     */
    public function update(User $user, Property $property): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasPermissionTo('manage-properties') && $property->user_id === $user->id;
    }

    /**
     * Admins can delete any property. Agents can only delete their own.
     */
    public function delete(User $user, Property $property): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasPermissionTo('manage-properties') && $property->user_id === $user->id;
    }
}
