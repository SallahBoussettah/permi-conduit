<?php

namespace App\Observers;

use App\Models\User;
use App\Models\School;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Update school candidate count if user is a candidate with school
        if ($this->isCandidateWithSchool($user)) {
            $this->updateSchoolCount($user->school_id);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if relevant fields changed (role_id, school_id, is_active, approval_status)
        if ($user->isDirty('role_id') || $user->isDirty('school_id') || 
            $user->isDirty('is_active') || $user->isDirty('approval_status')) {
            
            // Update count for old school if it changed and user was a candidate
            if ($user->isDirty('school_id') && $user->getOriginal('school_id')) {
                $originalRoleId = $user->getOriginal('role_id');
                $candidateRoleId = $this->getCandidateRoleId();
                
                if ($originalRoleId === $candidateRoleId) {
                    $this->updateSchoolCount($user->getOriginal('school_id'));
                }
            }
            
            // Update count for current school if user is a candidate
            if ($this->isCandidateWithSchool($user)) {
                $this->updateSchoolCount($user->school_id);
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Update school candidate count if user is a candidate with school
        if ($this->isCandidateWithSchool($user)) {
            $this->updateSchoolCount($user->school_id);
        }
    }

    /**
     * Check if user is a candidate with an assigned school
     */
    private function isCandidateWithSchool(User $user): bool
    {
        $candidateRoleId = $this->getCandidateRoleId();
        return $user->role_id === $candidateRoleId && !empty($user->school_id);
    }
    
    /**
     * Get the candidate role ID from the database or cache
     */
    private function getCandidateRoleId(): ?int
    {
        // Use cache to avoid repeated database queries
        return \Cache::remember('candidate_role_id', 3600, function () {
            $role = \App\Models\Role::where('name', 'candidate')->first();
            return $role ? $role->id : null;
        });
    }
    
    /**
     * Update the active candidate count for a school
     */
    private function updateSchoolCount(int $schoolId): void
    {
        $school = School::find($schoolId);
        if ($school) {
            $school->updateActiveCandidateCount();
        }
    }
} 