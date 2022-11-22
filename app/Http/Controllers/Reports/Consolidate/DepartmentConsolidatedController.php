<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{
    DepartmentEmployee,
    Report,
    User,
    Authentication\UserRole,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
};
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
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();

        $currentQuarterYear = Quarter::find(1);
        $quarter = $currentQuarterYear->current_quarter;
        $year = $currentQuarterYear->current_year;

        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $department_accomps =
            Report::select(
                            'reports.*',
                            'report_categories.name as report_category',
                            'users.last_name',
                            'users.first_name',
                            'users.middle_name',
                            'users.suffix'
                          )
                ->join('report_categories', 'reports.report_category_id', 'report_categories.id')
                ->join('users', 'users.id', 'reports.user_id')
                ->where('reports.report_year', $year)
                ->where('reports.report_quarter', $quarter)
                ->where('reports.department_id', $id)
                ->orderBy('reports.updated_at', 'DESC')
                ->get();

        //get_department_and_college_name
        $college_names = [];
        $department_names = [];
        foreach($department_accomps as $row){
            $temp_college_name = College::select('name')->where('id', $row->college_id)->first();
            $temp_department_name = Department::select('name')->where('id', $row->department_id)->first();
            $row->report_details = json_decode($row->report_details, false);

            if($temp_college_name == null)
                $college_names[$row->id] = '-';
            else
                $college_names[$row->id] = $temp_college_name->name;
            if($temp_department_name == null)
                $department_names[$row->id] = '-';
            else
            $department_names[$row->id] = $temp_department_name->name;
        }

        $employees = DepartmentEmployee::where('department_employees.department_id', $id)->join('users', 'users.id', 'department_employees.user_id')->get();
        $user = User::find(auth()->id());
        //departmentdetails
        $department = Department::find($id);
        return view(
                    'reports.consolidate.department',
                    compact('roles', 'department_accomps', 'department', 'department_names', 'college_names', 
                    'year', 'quarter', 'user', 'id', 'assignments', 'employees')
                );
    }

    public function departmentReportYearFilter($dept, $year, $quarter) {
        if ($year == "default") {
            return redirect()->route('reports.consolidate.department');
        }
        else {
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $department_accomps =
                Report::where('reports.report_year', $year)
                    ->where('reports.report_quarter', $quarter)
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

            //get_department_and_college_name
            $college_names = [];
            $department_names = [];
            foreach($department_accomps as $row){
                $temp_college_name = College::select('name')->where('id', $row->college_id)->first();
                $temp_department_name = Department::select('name')->where('id', $row->department_id)->first();
                $row->report_details = json_decode($row->report_details, false);

                if($temp_college_name == null)
                    $college_names[$row->id] = '-';
                else
                    $college_names[$row->id] = $temp_college_name->name;
                if($temp_department_name == null)
                    $department_names[$row->id] = '-';
                else
                $department_names[$row->id] = $temp_department_name->name;
            }

            $user = User::find(auth()->id());
            //departmentdetails
            $department = Department::find($dept);
            $id = $dept;
            return view(
                'reports.consolidate.department',
                compact('roles', 'department_accomps', 'department', 'department_names', 'college_names', 
                'year', 'quarter', 'user', 'id', 'assignments')
            );
        }
    }
}
