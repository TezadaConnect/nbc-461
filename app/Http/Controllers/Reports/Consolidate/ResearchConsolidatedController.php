<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Authentication\UserRole;
use App\Models\FormBuilder\DropdownOption;
use App\Models\Maintenance\College;
use App\Models\Maintenance\Quarter;
use App\Services\CommonService;
use App\Services\ManageConsolidatedReportAuthorizationService;

class ResearchConsolidatedController extends Controller
{
    private $commonService;

    public function __construct(CommonService $commonService){
        $this->commonService = $commonService;
    }

    public function index($id){
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageConsolidatedReportsByResearch();
        if (!($authorize)) { abort(403, 'Unauthorized action.'); }
        $currentQuarterYear = Quarter::find(1);
        $year = $currentQuarterYear->current_year;
        /************/
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $department_accomps =
            Report::whereIn('reports.report_category_id', [1, 2, 3, 4, 5, 6, 7])
                ->where('reports.report_year', $year)
                ->where('reports.format', 'f')
                // ->where('reports.research_cluster_id', $id)
                ->where('reports.college_id', $id)
                ->select(
                            'reports.*',
                            'report_categories.name as report_category',
                            'users.last_name',
                            'users.first_name',
                            'users.middle_name',
                            'users.suffix'
                          )
                ->where('reports.format', 'f')
                ->join('report_categories', 'reports.report_category_id', 'report_categories.id')
                ->join('users', 'users.id', 'reports.user_id')
                ->orderBy('reports.updated_at', 'DESC')
                ->get();
        //Get department tagged in each report
        $department_names = $this->commonService->getCollegeDepartmentNames($department_accomps)['department_names'];
        //Cluster record is in the dropdown as it appears in the research form
        // $cluster = DropdownOption::find($id); 
        $cluster = College::find($id); 
        return view('reports.consolidate.research', compact('roles','department_accomps', 'cluster', 'year', 'id', 'assignments',
        'department_names'));
    }

    public function departmentResReportYearFilter($clusterID, $year) {
        if ($year == "default") { return redirect()->route('reports.consolidate.research'); }
        else {
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $department_accomps = Report::whereIn('reports.report_category_id', [1, 2, 3, 4, 5, 6, 7])
                ->where('reports.report_year', $year)
                ->where('reports.format', 'f')
                ->where('reports.college_id', $clusterID)
                // ->where('reports.research_cluster_id', $clusterID)
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
            $department_names = $this->commonService->getCollegeDepartmentNames($department_accomps)['department_names'];
            // $cluster = DropdownOption::find($clusterID);
            $cluster = College::find($clusterID); 
            $id = $clusterID;
            return view('reports.consolidate.research', compact('roles','department_accomps', 'cluster', 'department_names',
                'year', 'id', 'assignments'
            ));
        }
    }
}
