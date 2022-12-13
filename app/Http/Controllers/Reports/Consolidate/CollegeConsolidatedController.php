<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Associate,
    Chairperson,
    Dean,
    FacultyResearcher,
    FacultyExtensionist,
    Report,
    SectorHead,
    User,
    Authentication\UserRole,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
};
use App\Services\CommonService;
use App\Services\ManageConsolidatedReportAuthorizationService;

class CollegeConsolidatedController extends Controller
{
    private $commonService;

    public function __construct(CommonService $commonService){
        $this->commonService = $commonService;
    }

    public function index($id){
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageConsolidatedReportsByCollege();
        if (!($authorize)) { abort(403, 'Unauthorized action.'); }
        $currentQuarterYear = Quarter::find(1);
        $quarter = $currentQuarterYear->current_quarter;
        $quarter2 = $currentQuarterYear->current_quarter;
        $year = $currentQuarterYear->current_year;
        /************/
        $user = User::find(auth()->id());
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $college_accomps =
            Report::where('reports.report_year', $year)
                ->where('reports.report_quarter', $quarter)
                ->where('reports.college_id', $id)
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
        $department_names = $this->commonService->getCollegeDepartmentNames($college_accomps)['department_names'];
        $employees = User::join('employees', 'employees.user_id', 'users.id')->where('employees.college_id', $id)->select('users.*')->distinct()->get();
        $departments = Department::where('college_id', $id)->get(); //Get departments linked to the college as options for exporting dept-level QAR
        $college = College::find($id);
        return view('reports.consolidate.college', compact('roles', 'college_accomps', 'college' , 'department_names', 
            'quarter', 'quarter2', 'year', 'id', 'user', 'assignments', 'employees', 'departments'
        ));
    }

    public function collegeReportYearFilter($college, $year, $quarter, $quarter2)
    {
        if ($year == "default") { return redirect()->route('reports.consolidate.college'); } 
        else {
            $user = User::find(auth()->id());
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $college_accomps =
                Report::where('reports.report_year', $year)
                    ->whereBetween('reports.report_quarter', [$quarter, $quarter2])
                    ->where('reports.college_id', $college)
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
            $department_names = $this->commonService->getCollegeDepartmentNames($college_accomps)['department_names'];
            $employees = User::join('employees', 'employees.user_id', 'users.id')->where('employees.college_id', $college)->select('users.*')->distinct()->get();
            $departments = Department::where('college_id', $college)->get(); //Get departments linked to the college as options for exporting dept-level QAR
            $college = College::find($college);
            $id = $college->id; //Labeled as ID to be passed in Generate Controller.
            return view('reports.consolidate.college', compact('roles', 'college_accomps', 'college', 'department_names', 
                'quarter', 'quarter2', 'year', 'id', 'user', 'assignments', 'employees', 'departments'
            ));
        }
    }
}
