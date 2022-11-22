<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    Associate,
    Chairperson,
    Dean,
    FacultyExtensionist,
    FacultyResearcher,
    Report,
    SectorHead,
    Authentication\UserRole,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
};
use App\Models\FormBuilder\DropdownOption;
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
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        $currentQuarterYear = Quarter::find(1);
        $year = $currentQuarterYear->current_year;

        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();

        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $department_accomps =
            Report::whereIn('reports.report_category_id', [1, 2, 3, 4, 5, 6, 7])
                ->where('reports.report_year', $year)
                ->where('reports.research_cluster_id', $id)
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

        //cluster Record
        $cluster = DropdownOption::find($id);
        return view(
                    'reports.consolidate.research',
                    compact('roles','department_accomps', 'cluster' , 'department_names',
                        'college_names', 'year', 'id', 'assignments')
                );
    }

    public function departmentResReportYearFilter($clusterID, $year) {
        if ($year == "default") {
            return redirect()->route('reports.consolidate.research');
        }
        else {
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $department_accomps = Report::whereIn('reports.report_category_id', [1, 2, 3, 4, 5, 6, 7])
                ->where('reports.report_year', $year)
                ->where('reports.research_cluster_id', $clusterID)
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

            //cluster Record
            $cluster = DropdownOption::find($clusterID);
            $id = $clusterID;
            return view(
                'reports.consolidate.research',
                compact('roles','department_accomps', 'cluster' , 'department_names',
                    'college_names', 'year', 'id', 'assignments')
            );
        }
    }
}
