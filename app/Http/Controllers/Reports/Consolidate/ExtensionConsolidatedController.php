<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Authentication\UserRole;
use App\Models\Maintenance\College;
use App\Models\Maintenance\Quarter;
use App\Services\CommonService;
use App\Services\ManageConsolidatedReportAuthorizationService;

class ExtensionConsolidatedController extends Controller
{
    private $commonService;

    public function __construct(CommonService $commonService){
        $this->commonService = $commonService;
    }

    public function index($id){
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageConsolidatedReportsByExtension();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }
        $currentQuarterYear = Quarter::find(1);
        $year = $currentQuarterYear->current_year;
        /************/
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $department_accomps =
            Report::whereIn('reports.report_category_id', [12, 13, 14, 22, 23, 37])
                ->where('reports.format', 'f')
                ->where('reports.report_year', $year)
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
        $department_names = $this->commonService->getCollegeDepartmentNames($department_accomps)['department_names'];
        $college = College::find($id); //Get college record
        return view('reports.consolidate.extension', compact('roles', 'department_accomps', 'college' , 'department_names',
            'year', 'id', 'assignments'
        ));
    }

    public function departmentExtReportYearFilter($clusterID, $year) {
        if ($year == "default") { return redirect()->route('reports.consolidate.extension'); }
        else {
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $department_accomps =
                Report::whereIn('reports.report_category_id', [12, 13, 14, 22, 23, 37])
                    ->where('reports.format', 'f')
                    ->where('reports.report_year', $year)
                    ->where('reports.college_id', $clusterID)
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

            $department_names = $this->commonService->getCollegeDepartmentNames($department_accomps)['department_names'];
            $college = College::find($clusterID);
            $id = $clusterID;
            return view('reports.consolidate.extension', compact('roles', 'department_accomps', 'college' , 'department_names',
                'year', 'id', 'assignments'
            ));
        }
    }
}
