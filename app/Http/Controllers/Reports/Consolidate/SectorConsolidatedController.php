<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use App\Models\{
    Report,
    User,
    Authentication\UserRole,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
    Maintenance\Sector,
};
use App\Services\CommonService;
use App\Services\ManageConsolidatedReportAuthorizationService;

class SectorConsolidatedController extends Controller
{
    private $commonService;

    public function __construct(CommonService $commonService){
        $this->commonService = $commonService;
    }

    public function index($id){
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageConsolidatedReportsBySector();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();

        $currentQuarterYear = Quarter::find(1);
        $quarter = $currentQuarterYear->current_quarter;
        $quarter2 = $currentQuarterYear->current_quarter;
        $year = $currentQuarterYear->current_year;

        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $sector_accomps =
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
                ->where('reports.sector_id', $id)
                ->orderBy('reports.updated_at', 'DESC')
                ->get();

        //get_department_and_college_name
        $college_names = [];
        $department_names = [];
        foreach($sector_accomps as $row){
            $temp_college_name = College::select('name')->where('id', $row->college_id)->first();
            // $temp_college_name = College::where('id', $row->college_id)->first();
            $row->report_details = json_decode($row->report_details, false);
            $temp_department_name = Department::select('name')->where('id', $row->department_id)->first();

            // $temp_department_name = $temp_college_name->department()->where('id', $row->department_id)->pluck('name')->first();
            // dd($temp_department_name);
            if($temp_college_name == null)
                $college_names[$row->id] = '-';
            else
                $college_names[$row->id] = $temp_college_name->name;
            if($temp_department_name == null)
                $department_names[$row->id] = '-';
            else
            $department_names[$row->id] = $temp_department_name->name;
        }

        $employees = User::all();
        $departments = Department::all();
        $colleges = College::all();
        $sector = Sector::find($id);

        return view(
                    'reports.consolidate.sector',
                    compact('roles', 'sector_accomps', 'employees', 'departments', 'colleges', 'sector', 'department_names', 'college_names', 'quarter', 'quarter2', 'year', 'assignments')
                );
    }

    public function sectorReportYearFilter($sector, $year, $quarter, $quarter2) {
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageConsolidatedReportsBySector();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        if ($year == "default") {
            return redirect()->route('reports.consolidate.sector');
        } else{
        }
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $sector_accomps =
            Report::where('reports.report_year', $year)
                ->whereBetween('reports.report_quarter', [$quarter, $quarter2])
                ->where('reports.sector_id', $sector)
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
        foreach($sector_accomps as $row){
            $temp_college_name = College::select('name')->where('id', $row->college_id)->first();
            // $temp_college_name = College::where('id', $row->college_id)->first();
            $row->report_details = json_decode($row->report_details, false);
            $temp_department_name = Department::select('name')->where('id', $row->department_id)->first();

            // $temp_department_name = $temp_college_name->department()->where('id', $row->department_id)->pluck('name')->first();
            // dd($temp_department_name);
            if($temp_college_name == null)
                $college_names[$row->id] = '-';
            else
                $college_names[$row->id] = $temp_college_name->name;
            if($temp_department_name == null)
                $department_names[$row->id] = '-';
            else
            $department_names[$row->id] = $temp_department_name->name;
        }

        //SectorDetails
        $sector = Sector::find($sector);
        return view(
            'reports.consolidate.sector',
            compact('roles', 'sector_accomps', 'sector', 'department_names', 'college_names', 'quarter', 'quarter2', 'year', 'assignments')
        );
    }
}
