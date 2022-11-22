<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    Associate,
    Dean,
    Chairperson,
    FacultyExtensionist,
    FacultyResearcher,
    Report,
    SectorHead,
    Authentication\UserRole,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
};
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

        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();

        $currentQuarterYear = Quarter::find(1);
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
                ->whereIn('reports.report_category_id', [9, 10, 11, 12, 13, 14, 23, 34, 35, 36, 37])
                ->where('reports.report_year', $year)
                ->where('reports.college_id', $id)
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

        //get College record
        $college = College::find($id);

        return view(
                    'reports.consolidate.extension',
                    compact('roles', 'department_accomps', 'college' , 'department_names',
                        'college_names', 'year', 'id', 'assignments')
                );
    }

    public function departmentExtReportYearFilter($clusterID, $year) {
        if ($year == "default") {
            return redirect()->route('reports.consolidate.extension');
        }
        else {
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $department_accomps =
                Report::whereIn('reports.report_category_id', [9, 10, 11, 12, 13, 14, 23, 34, 35, 36, 37])
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


            //departmentdetails
            $college = College::find($clusterID);
            $id = $clusterID;
            return view(
                'reports.consolidate.extension',
                compact('roles', 'department_accomps', 'college' , 'department_names',
                    'college_names', 'year', 'id', 'assignments')
            );
        }
    }
}
