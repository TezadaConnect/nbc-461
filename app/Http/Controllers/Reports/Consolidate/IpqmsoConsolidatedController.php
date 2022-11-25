<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use App\Models\Associate;
use App\Models\Dean;
use App\Models\Chairperson;
use App\Models\FacultyExtensionist;
use App\Models\FacultyResearcher;
use App\Models\Report;
use App\Models\SectorHead;
use App\Models\User;
use App\Models\Maintenance\Sector;
use App\Models\Authentication\UserRole;
use App\Models\Maintenance\College;
use App\Models\Maintenance\Department;
use App\Models\Maintenance\Quarter;
use App\Services\CommonService;
use App\Services\ManageConsolidatedReportAuthorizationService;

class IpqmsoConsolidatedController extends Controller
{
    private $approvalHolderArr = [
        'researcher_approval',
        'extensionist_approval',
        'chairperson_approval',
        'dean_approval',
        'sector_approval',
        'ipqmso_approval'
    ];
    private $commonService;

    public function __construct(CommonService $commonService){
        $this->commonService = $commonService;
    }

    public function index(){
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageAllConsolidatedReports();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }
        $currentQuarterYear = Quarter::find(1);
        $quarter = $currentQuarterYear->current_quarter;
        $year = $currentQuarterYear->current_year;
        $quarter2 = 0;
        /************/
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $ipqmso_accomps =
            Report::where('reports.report_year', $year)
            ->where('reports.report_quarter', $quarter)
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
        //Get college tagged in each report
        $college_names = $this->commonService->getCollegeDepartmentNames($ipqmso_accomps)['college_names'];
        //Get department tagged in each report
        $department_names = $this->commonService->getCollegeDepartmentNames($ipqmso_accomps)['department_names'];
        $employees = User::all();
        $departments = Department::all();
        $colleges = College::get();
        $sectors = Sector::all();
        return view(
            'reports.consolidate.ipqmso', compact('roles', 'ipqmso_accomps', 'year', 'quarter', 'quarter2', 'employees', 
            'departments', 'colleges', 'sectors', 'assignments', 'college_names', 'department_names',
        ));
    }

    public function reportYearFilter($year, $quarter, $quarter2) {
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageAllConsolidatedReports();
        if (!($authorize)) { abort(403, 'Unauthorized action.'); }
        if ($year == "default") { return redirect()->route('reports.consolidate.ipqmso'); }
        else {
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $ipqmso_accomps =
                Report::where('reports.report_year', $year)
                    ->whereBetween('reports.report_quarter', [$quarter, $quarter2])
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
                    ->get();
            $college_names = $this->commonService->getCollegeDepartmentNames($ipqmso_accomps)['college_names'];
            $department_names = $this->commonService->getCollegeDepartmentNames($ipqmso_accomps)['department_names'];
            $sector_names = Sector::all();
        }

        return view(
            'reports.consolidate.ipqmso', compact('roles', 'ipqmso_accomps', 'department_names', 'college_names',
            'year', 'quarter', 'quarter2', 'sector_names', 'assignments', 'college_names', 'department_names')
        );
    }

    public function generatePendingList($pending = null)
    {
        $currentQuarterYear = Quarter::find(1);
        $quarter = $currentQuarterYear->current_quarter;
        $quarter2 = $currentQuarterYear->current_quarter;
        $year = $currentQuarterYear->current_year;
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $ipqmso_accomps =
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
            ->get();

        //Get college tagged in each report
        $college_names = $this->commonService->getCollegeDepartmentNames($ipqmso_accomps)['college_names'];
        //Get department tagged in each report
        $department_names = $this->commonService->getCollegeDepartmentNames($ipqmso_accomps)['department_names'];
        $employees = User::all();
        $departments = Department::all();
        $colleges = College::get();
        $sectors = Sector::all();
        $ipqmso_accomps = $this->commonService->getStatusOfIPO($ipqmso_accomps, $this->approvalHolderArr[$pending] ?? 'researcher_approval');

        return view(
            'reports.consolidate.ipqmso', compact('roles', 'ipqmso_accomps', 'year', 'quarter', 'quarter2', 'employees', 
            'departments', 'colleges', 'sectors', 'assignments', 'college_names', 'department_names', 'pending'
        ));
    }

    // private function AuthenticateUserLogged()
    // {
    //     $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageAllConsolidatedReports();
    //     if (!($authorize)) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
    //     $departments = [];
    //     $colleges = [];
    //     $sectors = [];
    //     $departmentsResearch = [];
    //     $departmentsExtension = [];
    //     $collegesForAssociate = [];
    //     $sectorsForAssistant = [];

    //     $currentQuarterYear = Quarter::find(1);
    //     $quarter = $currentQuarterYear->current_quarter;
    //     $year = $currentQuarterYear->current_year;

    //     if (in_array(5, $roles)) {
    //         $departments = Chairperson::where('chairpeople.user_id', auth()->id())->select('chairpeople.department_id', 'departments.code')
    //             ->join('departments', 'departments.id', 'chairpeople.department_id')->get();
    //     }
    //     if (in_array(6, $roles)) {
    //         $colleges = Dean::where('deans.user_id', auth()->id())->select('deans.college_id', 'colleges.code')
    //             ->join('colleges', 'colleges.id', 'deans.college_id')->get();
    //     }
    //     if (in_array(7, $roles)) {
    //         $sectors = SectorHead::where('sector_heads.user_id', auth()->id())->select('sector_heads.sector_id', 'sectors.code')
    //             ->join('sectors', 'sectors.id', 'sector_heads.sector_id')->get();
    //     }
    //     if (in_array(10, $roles)) {
    //         $departmentsResearch = FacultyResearcher::where('faculty_researchers.user_id', auth()->id())
    //             ->select('faculty_researchers.college_id', 'colleges.code')
    //             ->join('colleges', 'colleges.id', 'faculty_researchers.college_id')->get();
    //     }
    //     if (in_array(11, $roles)) {
    //         $departmentsExtension = FacultyExtensionist::where('faculty_extensionists.user_id', auth()->id())
    //             ->select('faculty_extensionists.college_id', 'colleges.code')
    //             ->join('colleges', 'colleges.id', 'faculty_extensionists.college_id')->get();
    //     }
    //     if (in_array(12, $roles)) {
    //         $colleges = Associate::where('associates.user_id', auth()->id())->select('associates.college_id', 'colleges.code')
    //             ->join('colleges', 'colleges.id', 'associates.college_id')->get();
    //     }
    //     if (in_array(13, $roles)) {
    //         $sectors = Associate::where('associates.user_id', auth()->id())->select('associates.sector_id', 'sectors.code')
    //             ->join('sectors', 'sectors.id', 'associates.sector_id')->get();
    //     }

    //     return [
    //         'roles' => $roles,
    //         'departments' => $departments,
    //         'colleges' => $colleges,
    //         'sectors' => $sectors,
    //         'departmentsResearch' => $departmentsResearch,
    //         'departmentsExtension' => $departmentsExtension,
    //         'quarter' => $quarter,
    //         'year' => $year
    //     ];
    // }
}
