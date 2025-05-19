<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    /**
     * Check if the user has access to the school of the given model.
     *
     * @param mixed $model
     * @return bool
     */
    protected function checkSchoolAccess($model)
    {
        $user = Auth::user();
        
        // If the model doesn't have a school_id, allow access
        if (!isset($model->school_id)) {
            return true;
        }
        
        // If the user is a super admin, allow access to all schools
        if ($user->role->name === 'super_admin') {
            return true;
        }
        
        // Otherwise, check if the user belongs to the model's school
        return $user->school_id === $model->school_id;
    }
}
