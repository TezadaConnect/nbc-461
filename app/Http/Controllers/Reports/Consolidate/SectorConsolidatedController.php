<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Authentication\UserRole;
use App\Models\Maintenance\College;
use App\Models\Maintenance\Department;
use App\Models\Maintenance\Quarter;
use App\Models\Maintenance\Sector;
use App\Services\CommonService;
use App\Services\ManageConsolidatedReportAuthorizationService;

class SectorConsolidatedController extends Controller
{
    private $commonService;

    public function __construct(CommonService $commonService){
        $this->commonService = $commonService;
    }

    public function index($id){
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageConsolidatedReportsBySector();
        if (!($authorize)) { abort(403, 'Unauthorized action.'); }
        $currentQuarterYear = Quarter::find(1);
        $quarter = $currentQuarterYear->current_quarter;
        $quarter2 = $currentQuarterYear->current_quarter;
        $year = $currentQuarterYear->current_year;
        /************/
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $sector_accomps =
            Report::where('reports.report_year', $year)
                ->whereBetween('reports.report_quarter', [$quarter, $quarter2])
                ->where('reports.sector_id', $id)
                ->select(
                    'reports.*',
                    'report_categories.name as report_category',
                    'users.last_name',
                    'users.first_name',
                    'users.middle_name',
                    'users.suffix'
                )
                ->join('report_categories', 'reports.report_category_id', 'report_categories.id')
                ->join('users', 'users.id', 'reports.user_id')
                ->orderBy('reports.updated_at', 'DESC')
                ->get();

        //Get department tagged in each report
        $department_names = $this->commonService->getCollegeDepartmentNames($sector_accomps)['department_names'];
        $employees = User::all();
        $departments = Department::all();
        $colleges = College::all();
        $sector = Sector::find($id);
        return view('reports.consolidate.sector', compact('roles', 'sector_accomps', 'employees', 'departments', 'colleges', 
            'sector', 'department_names', 'quarter', 'quarter2', 'year', 'assignments'
        ));
    }

    public function sectorReportYearFilter($sector, $year, $quarter, $quarter2) {
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageConsolidatedReportsBySector();
        if (!($authorize)) { abort(403, 'Unauthorized action.'); }
        if ($year == "default") { return redirect()->route('reports.consolidate.sector'); } 
        else{
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $sector_accomps =
                Report::where('reports.report_year', $year)
                ->whereBetween('reports.report_quarter', [$quarter, $quarter2])
                ->where('reports.sector_id', $sector)
                ->select(
                            'reports.*',
                            'report_categories.name as report_category',
                            'users.last_name',
                            'users.first_name',
                            'users.middle_name',
                            'users.suffix'
                          )
                ->join('report_categories', 'reports.report_category_id', 'report_categories.id')
                ->join('users', 'users.id', 'reports.user_id')
                ->orderBy('reports.updated_at', 'DESC')
                ->get();

            $department_names = $this->commonService->getCollegeDepartmentNames($sector_accomps)['department_names'];
            $sector = Sector::find($sector);
        }
        return view('reports.consolidate.sector', compact('roles', 'sector_accomps', 'sector', 'department_names', 
            'quarter', 'quarter2', 'year', 'assignments',
        ));
    }
}
