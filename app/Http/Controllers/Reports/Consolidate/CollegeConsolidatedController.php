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
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();

        $currentQuarterYear = Quarter::find(1);
        $quarter = $currentQuarterYear->current_quarter;
        $year = $currentQuarterYear->current_year;

        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $college_accomps =
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
            ->where('reports.college_id', $id)
            ->orderBy('reports.updated_at', 'DESC')
            ->get();

        //get_department_and_college_name
        $college_names = [];
        $department_names = [];
        foreach ($college_accomps as $row) {
            $temp_college_name = College::select('name')->where('id', $row->college_id)->first();
            $temp_department_name = Department::select('name')->where('id', $row->department_id)->first();
            $row->report_details = json_decode($row->report_details, false);

            if ($temp_college_name == null)
                $college_names[$row->id] = '-';
            else
                $college_names[$row->id] = $temp_college_name->name;
            if ($temp_department_name == null)
                $department_names[$row->id] = '-';
            else
                $department_names[$row->id] = $temp_department_name->name;
        }

        $user = User::find(auth()->id());
        //collegedetails
        $college = College::find($id);

        return view(
                    'reports.consolidate.college',
                    compact('roles', 'college_accomps', 'college' ,
                        'department_names', 'college_names', 'quarter', 'year', 'id', 'user', 'assignments')
                );
    }

    public function collegeReportYearFilter($college, $year, $quarter)
    {
        if ($year == "default") {
            return redirect()->route('reports.consolidate.college');
        } else {
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
            $college_accomps =
                Report::where('reports.report_year', $year)
                    ->where('reports.report_quarter', $quarter)
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

            //get_department_and_college_name
            $college_names = [];
            $department_names = [];
            foreach ($college_accomps as $row) {
                $temp_college_name = College::select('name')->where('id', $row->college_id)->first();
                $temp_department_name = Department::select('name')->where('id', $row->department_id)->first();
                $row->report_details = json_decode($row->report_details, false);
                if ($temp_college_name == null)
                    $college_names[$row->id] = '-';
                else
                    $college_names[$row->id] = $temp_college_name->name;
                if ($temp_department_name == null)
                    $department_names[$row->id] = '-';
                else
                    $department_names[$row->id] = $temp_department_name->name;
            }

            $user = User::find(auth()->id());
            //collegedetails
            $college = College::find($college);
            $id = $college->id;
            return view(
                'reports.consolidate.college',
                compact('roles', 'college_accomps', 'college' ,
                    'department_names', 'college_names', 'quarter', 'year', 'id', 'user', 'assignments')
            );
        }
    }
}
