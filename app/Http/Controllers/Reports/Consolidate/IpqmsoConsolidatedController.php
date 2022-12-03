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

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function index()
    {
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
        $ipqmso_accomps = [];
        // Report::where('reports.report_year', $year)
        // ->where('reports.report_quarter', $quarter)
        // ->select(
        //     'reports.*',
        //     'report_categories.name as report_category',
        //     'users.last_name',
        //     'users.first_name',
        //     'users.middle_name',
        //     'users.suffix'
        // )
        // ->join('report_categories', 'reports.report_category_id', 'report_categories.id')
        // ->join('users', 'users.id', 'reports.user_id')
        // ->orderBy('reports.updated_at', 'DESC')
        // ->get();
        //Get college tagged in each report
        $college_names = $this->commonService->getCollegeDepartmentNames($ipqmso_accomps)['college_names'];
        //Get department tagged in each report
        $department_names = $this->commonService->getCollegeDepartmentNames($ipqmso_accomps)['department_names'];
        $employees = User::all();
        $departments = Department::all();
        $colleges = College::get();
        $sectors = Sector::all();
        return view(
            'reports.consolidate.ipqmso',
            compact(
                'roles',
                'ipqmso_accomps',
                'year',
                'quarter',
                'quarter2',
                'employees',
                'departments',
                'colleges',
                'sectors',
                'assignments',
                'college_names',
                'department_names',
            )
        );
    }

    public function reportYearFilter($year, $quarter, $quarter2)
    {
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageAllConsolidatedReports();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }
        if ($year == "default") {
            return redirect()->route('reports.consolidate.ipqmso');
        } else {
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
            foreach ($ipqmso_accomps as $item) $item->report_details = json_decode($item->report_details);
            $college_names = $this->commonService->getCollegeDepartmentNames($ipqmso_accomps)['college_names'];
            $department_names = $this->commonService->getCollegeDepartmentNames($ipqmso_accomps)['department_names'];
            $sectors = Sector::all();
            $employees = User::all();
            $departments = Department::all();
            $colleges = College::get();
        }

        return view(
            'reports.consolidate.ipqmso',
            compact(
                'roles',
                'ipqmso_accomps',
                'department_names',
                'college_names',
                'year',
                'quarter',
                'quarter2',
                'sectors',
                'assignments',
                'college_names',
                'department_names',
                'employees',
                'departments',
                'colleges'
            )
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
            'reports.consolidate.ipqmso',
            compact(
                'roles',
                'ipqmso_accomps',
                'year',
                'quarter',
                'quarter2',
                'employees',
                'departments',
                'colleges',
                'sectors',
                'assignments',
                'college_names',
                'department_names',
                'pending'
            )
        );
    }
}
