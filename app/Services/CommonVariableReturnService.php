<?php 
// =============================================================================================
// TITLE: COMMON VARIABLE RETURN SERVICE
// DESCRIPTION: USED FOR HANDLING REPETITIVE VARIABLES ACROSS THE SYSTEM
// DEVELOPER: KENYLEEN D. PAN
// DATE: NOVEMBER 11, 2022
// =============================================================================================

namespace App\Services;

use App\Models\Authentication\UserRole;
use App\Models\Maintenance\Quarter;
use App\Models\Maintenance\ReportCategory;
use App\Models\User;

class CommonVariableReturnService{
    public function getConsolidationVar(){
        $quarter = Quarter::find(1)->current_quarter;
        $quarter2 = Quarter::find(1)->current_quarter;
        $year = Quarter::find(1)->current_year;
        $user = User::find(auth()->id());
        $reportCategories = ReportCategory::all();
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        return ['year' => $year, 'quarter' => $quarter, 'quarter2' => $quarter2, 'user' => $user, 'assignments' => $assignments, 'reportCategories' => $reportCategories];
    }
}