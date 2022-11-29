<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Associate,
    Chairperson,
    Dean,
    Employee,
    Extensionist,
    FacultyExtensionist,
    FacultyResearcher,
    Report,
    SectorHead,
    User,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
    Maintenance\ReportCategory,
    Researcher,
};
use App\Models\Authentication\UserRole;
use App\Services\CommonService;
use App\Services\ManageConsolidatedReportAuthorizationService;

class MyAccomplishmentController extends Controller
{
    private $commonService;

    public function __construct(CommonService $commonService){
        $this->commonService = $commonService;
    }

    public function index() {
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageConsolidatedIndividualReports();
        if (!($authorize)) { abort(403, 'Unauthorized action.'); }
        $currentQY = Quarter::find(1);
        $quarter = $currentQY->current_quarter;
        $quarter2 = $currentQY->current_quarter;
        $year = $currentQY->current_year;
        /************/
        $user = User::find(auth()->id());
        $report_categories = ReportCategory::all();
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles); //determine the assigned offices/area of consolidation
        //get my individual accomplishment
        $my_accomplishments =
            Report::where('reports.report_year', $year)
                ->where('reports.report_quarter', $quarter)
                ->where('reports.user_id', auth()->id())
                ->select('reports.*', 'report_categories.name as report_category')
                ->join('report_categories', 'reports.report_category_id', 'report_categories.id')
                ->orderBy('reports.updated_at', 'DESC')
                ->get();

        //Get department tagged in each report
        $department_names = $this->commonService->getCollegeDepartmentNames($my_accomplishments)['department_names'];
        //Get distinct colleges based on the assignment of employee
        $collegeList = Employee::where('user_id', auth()->id())->join('colleges', 'colleges.id', 'employees.college_id')->select('colleges.*')->distinct()->get();

        return view(
            'reports.consolidate.myaccomplishments', compact(
                'roles','my_accomplishments', 'department_names', 'year', 'quarter', 'quarter2', 
                'report_categories', 'user', 'collegeList', 'assignments'
        ));
    }

    public function individualReportYearFilter($year, $quarter, $quarter2) {
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageConsolidatedIndividualReports();
        if (!($authorize)) { abort(403, 'Unauthorized action.'); }
        $currentQY = Quarter::find(1);
        $quarter = $currentQY->current_quarter;
        $quarter2 = $currentQY->current_quarter;
        $year = $currentQY->current_year;
        /************/
        $report_categories = ReportCategory::all();
        if ($year == "default") { return redirect()->route('submissions.myaccomp.index'); }
        else {
            $user = User::find(auth()->id());
            $report_categories = ReportCategory::all();
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $my_accomplishments = Report::where('reports.report_year', $this->getQuarterYear()['year'])
                    ->whereBetween('reports.report_quarter', [$this->quarter, $this->quarter2])
                    ->where('reports.user_id', auth()->id())
                    ->select('reports.*', 'report_categories.name as report_category')
                    ->join('report_categories', 'reports.report_category_id', 'report_categories.id')
                    ->orderBy('reports.updated_at', 'DESC')
                    ->get(); //get my individual accomplishment

            //Get department tagged in each report
            $department_names = $this->commonService->getCollegeDepartmentNames($my_accomplishments)['department_names'];
            //Get distinct colleges based on the assignment of employee
            $collegeList = Employee::where('user_id', auth()->id())->join('colleges', 'colleges.id', 'employees.college_id')->select('colleges.*')->distinct()->get();
            return view(
                'reports.consolidate.myaccomplishments', compact(
                    'roles','my_accomplishments', 'college_names', 'department_names',
                    'year', 'quarter', 'quarter2', 'report_categories', 'user', 'collegeList',
                    'assignments'
                ));
        }
    }
}
