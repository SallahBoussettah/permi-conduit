<?php

namespace App\Policies;

use App\Models\QcmPaper;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QcmPaperPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins, super admins, and inspectors can view QCM papers
        return $user->isAdmin() || $user->isSuperAdmin() || $user->isInspector();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QcmPaper $qcmPaper): bool
    {
        // Admins and super admins can view any QCM paper
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }
        
        // Inspectors can view QCM papers they created or papers associated with their school
        if ($user->isInspector()) {
            // Check if the user created the paper
            if ($qcmPaper->created_by === $user->id) {
                return true;
            }
            
            // Check if the paper is associated with the user's school
            if ($user->school_id && $qcmPaper->school_id === $user->school_id) {
                return true;
            }
            
            // Check if the user has access to the permit category
            return $user->hasPermitCategory($qcmPaper->permit_category_id);
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only inspectors can create QCM papers
        return $user->isInspector();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QcmPaper $qcmPaper): bool
    {
        // Admins and super admins can update any QCM paper
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }
        
        // Inspectors can only update papers they created or papers associated with their school
        if ($user->isInspector()) {
            // Check if the user created the paper
            if ($qcmPaper->created_by === $user->id) {
                return true;
            }
            
            // Check if the paper is associated with the user's school
            if ($user->school_id && $qcmPaper->school_id === $user->school_id) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QcmPaper $qcmPaper): bool
    {
        // Admins and super admins can delete any QCM paper
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }
        
        // Inspectors can only delete papers they created
        if ($user->isInspector() && $qcmPaper->created_by === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QcmPaper $qcmPaper): bool
    {
        // Only admins and super admins can restore QCM papers
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QcmPaper $qcmPaper): bool
    {
        // Only admins and super admins can permanently delete QCM papers
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
