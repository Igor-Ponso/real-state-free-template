<?php

namespace App\Policies;

use App\Models\Inquiry;
use App\Models\User;

/**
 * Authorization policy for inquiry management.
 *
 * Admins see all inquiries. Agents see only inquiries
 * on their own property listings.
 */
class InquiryPolicy
{
    /**
     * Admins and agents can view the inquiry list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage-inquiries');
    }

    /**
     * Admins see any inquiry. Agents see only inquiries on their properties.
     */
    public function view(User $user, Inquiry $inquiry): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasPermissionTo('manage-inquiries') && $inquiry->loadMissing('property')->property->user_id === $user->id;
    }

    /**
     * Admins can update any inquiry status. Agents can update only on their properties.
     */
    public function update(User $user, Inquiry $inquiry): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasPermissionTo('manage-inquiries') && $inquiry->loadMissing('property')->property->user_id === $user->id;
    }
}
