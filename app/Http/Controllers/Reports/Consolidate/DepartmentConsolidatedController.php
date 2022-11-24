<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use App\Models\DepartmentEmployee;
use App\Models\Report;
use App\Models\User;
use App\Models\Authentication\UserRole;
use App\Models\Maintenance\College;
use App\Models\Maintenance\Department;
use App\Models\Maintenance\Quarter;
use App\Services\CommonService;
use App\Services\ManageConsolidatedReportAuthorizationService;

class DepartmentConsolidatedController extends Controller
{
    private $commonService;

    public function __construct(CommonService $commonService){
        $this->commonService = $commonService;
    }

    public function index($id){
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageConsolidatedReportsByDepartment();
        if (!($authorize)) { abort(403, 'Unauthorized action.'); }
        $currentQuarterYear = Quarter::find(1);
        $quarter = $currentQuarterYear->current_quarter;
        $quarter2 = $currentQuarterYear->current_quarter;
        $year = $currentQuarterYear->current_year;
        /************/
        $user = User::find(auth()->id());
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $department_accomps =
            Report::where('reports.report_year', $year)
                ->where('reports.report_quarter', $quarter)
                ->where('reports.department_id', $id)
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

        $department = Department::find($id); //Get the dept. record.
        $colleges = College::all(); //Get all colleges as options for exporting individual QAR
        $employees = DepartmentEmployee::where('department_employees.department_id', $id)->join('users', 'users.id', 'department_employees.user_id')->get();
        return view(
                    'reports.consolidate.department',
                    compact('roles', 'department_accomps', 'department', 'department_names', 'college_names', 
                    'year', 'quarter', 'quarter2', 'user', 'id', 'assignments', 'employees', 'colleges')
                );
    }

    public function departmentReportYearFilter($dept, $year, $quarter, $quarter2) {
        if ($year == "default") {  return redirect()->route('reports.consolidate.department'); }
        else {
            $user = User::find(auth()->id());
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $department_accomps =
                Report::where('reports.report_year', $year)
                    ->whereBetween('reports.report_quarter', [$quarter, $quarter2])
                    ->where('reports.department_id', $dept)
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

            $department = Department::find($dept); //Get the dept. record
            $id = $dept; //Labeled as ID to be passed in Generate Controller.
            return view(
                'reports.consolidate.department',
                compact('roles', 'department_accomps', 'department', 'department_names', 'college_names', 
                'year', 'quarter', 'user', 'id', 'assignments')
            );
        }
    }
}
