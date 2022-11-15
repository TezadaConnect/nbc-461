<?php

namespace App\Http\Controllers\Reports\Consolidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Associate,
    Dean,
    Chairperson,
    FacultyExtensionist,
    FacultyResearcher,
    Report,
    SectorHead,
    Maintenance\Sector,
    Authentication\UserRole,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
};
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

        // $ipqmso_accomps =
        //     Report::select(
        //         'reports.*',
        //         'report_categories.name as report_category',
        //         'users.last_name',
        //         'users.first_name',
        //         'users.middle_name',
        //         'users.suffix'
        //     )
        //     ->join('report_categories', 'reports.report_category_id', 'report_categories.id')
        //     ->join('users', 'users.id', 'reports.user_id')
        //     ->where('reports.report_year', $year)
        //     ->where('reports.report_quarter', $quarter)
        //     ->orderBy('reports.updated_at', 'DESC')
        //     ->get();

        // //get_department_and_college_name
        // $college_names = [];
        // $department_names = [];
        // foreach ($ipqmso_accomps as $row) {
        //     $temp_college_name = College::select('name')->where('id', $row->college_id)->first();
        //     $temp_department_name = Department::select('name')->where('id', $row->department_id)->first();
        //     $row->report_details = json_decode($row->report_details, false);

        //     if ($temp_college_name == null)
        //         $college_names[$row->id] = '-';
        //     else
        //         $college_names[$row->id] = $temp_college_name->name;
        //     if ($temp_department_name == null)
        //         $department_names[$row->id] = '-';
        //     else
        //         $department_names[$row->id] = $temp_department_name->name;
        // }

        // 'ipqmso_accomps' , 'college_names'
        $authenticateUser = $this->AuthenticateUserLogged();
        $roles = $authenticateUser['roles'];
        $departments = $authenticateUser['departments'];
        $colleges = $authenticateUser['colleges'];
        $sectors = $authenticateUser['sectors'];
        $departmentsResearch = $authenticateUser['departmentsResearch'];
        $departmentsExtension = $authenticateUser['departmentsExtension'];
        $quarter = $authenticateUser['quarter'];
        $year = $authenticateUser['year'];



        $sector_names = Sector::all();
        // 
        return view(
            'reports.consolidate.ipqmso',
            compact('sector_names', 'roles', 'departments', 'colleges', 'sectors', 'departmentsResearch',  'departmentsExtension', 'quarter',  'year')
        );
    }

    public function reportYearFilter($year, $quarter)
    {
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageAllConsolidatedReports();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        if ($year == "default") {
            return redirect()->route('reports.consolidate.ipqmso');
        } else {
            $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
            $departments = [];
            $colleges = [];
            $sectors = [];
            $departmentsResearch = [];
            $departmentsExtension = [];

            if (in_array(5, $roles)) {
                $departments = Chairperson::where('chairpeople.user_id', auth()->id())->select('chairpeople.department_id', 'departments.code')
                    ->join('departments', 'departments.id', 'chairpeople.department_id')->get();
            }
            if (in_array(6, $roles)) {
                $colleges = Dean::where('deans.user_id', auth()->id())->select('deans.college_id', 'colleges.code')
                    ->join('colleges', 'colleges.id', 'deans.college_id')->get();
            }
            if (in_array(7, $roles)) {
                $sectors = SectorHead::where('sector_heads.user_id', auth()->id())->select('sector_heads.sector_id', 'sectors.code')
                    ->join('sectors', 'sectors.id', 'sector_heads.sector_id')->get();
            }
            if (in_array(10, $roles)) {
                $departmentsResearch = FacultyResearcher::where('faculty_researchers.user_id', auth()->id())
                    ->select('faculty_researchers.college_id', 'colleges.code')
                    ->join('colleges', 'colleges.id', 'faculty_researchers.college_id')->get();
            }
            if (in_array(11, $roles)) {
                $departmentsExtension = FacultyExtensionist::where('faculty_extensionists.user_id', auth()->id())
                    ->select('faculty_extensionists.college_id', 'colleges.code')
                    ->join('colleges', 'colleges.id', 'faculty_extensionists.college_id')->get();
            }

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

            //get_department_and_college_name
            $college_names = [];
            $department_names = [];
            foreach ($ipqmso_accomps as $row) {
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
        }
        $sector_names = Sector::all();

        return view(
            'reports.consolidate.ipqmso',
            compact('roles', 'departments', 'colleges', 'ipqmso_accomps', 'department_names', 'college_names', 'sectors', 'departmentsResearch', 'departmentsExtension', 'year', 'quarter', 'sector_names')
        );
    }

    public function generatePendingList($pending = null)
    {
        $authenticateUser = $this->AuthenticateUserLogged();
        $roles = $authenticateUser['roles'];
        $departments = $authenticateUser['departments'];
        $colleges = $authenticateUser['colleges'];
        $sectors = $authenticateUser['sectors'];
        $departmentsResearch = $authenticateUser['departmentsResearch'];
        $departmentsExtension = $authenticateUser['departmentsExtension'];
        $quarter = $authenticateUser['quarter'];
        $year = $authenticateUser['year'];

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


        $sector_names = Sector::all();


        $ipqmso_accomps = $this->commonService->getStatusOfIPO($ipqmso_accomps, $this->approvalHolderArr[$pending] ?? 'researcher_approval');

        return view(
            'reports.consolidate.ipqmso',
            compact('ipqmso_accomps', 'sector_names', 'roles', 'departments', 'colleges', 'sectors', 'departmentsResearch',  'departmentsExtension', 'quarter',  'year', 'pending')
        );
    }

    public function notifyPendingOffice($pending)
    {
        // $findPendingQry = Report::where($this->approvalHolderArr[$pending] ?? 'researcher_approval', 1)->get();
        // $idHolder = [];

        // $containerId

        // foreach ($findPendingQry as $value) {
        //     if (!in_array($value[], $idHolder)) {

        //         array_push($idHolder, $value[]);
        //     }
        // }
    }

    private function AuthenticateUserLogged()
    {
        $authorize = (new ManageConsolidatedReportAuthorizationService())->authorizeManageAllConsolidatedReports();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
        $departments = [];
        $colleges = [];
        $sectors = [];
        $departmentsResearch = [];
        $departmentsExtension = [];
        $collegesForAssociate = [];
        $sectorsForAssistant = [];

        $currentQuarterYear = Quarter::find(1);
        $quarter = $currentQuarterYear->current_quarter;
        $year = $currentQuarterYear->current_year;

        if (in_array(5, $roles)) {
            $departments = Chairperson::where('chairpeople.user_id', auth()->id())->select('chairpeople.department_id', 'departments.code')
                ->join('departments', 'departments.id', 'chairpeople.department_id')->get();
        }
        if (in_array(6, $roles)) {
            $colleges = Dean::where('deans.user_id', auth()->id())->select('deans.college_id', 'colleges.code')
                ->join('colleges', 'colleges.id', 'deans.college_id')->get();
        }
        if (in_array(7, $roles)) {
            $sectors = SectorHead::where('sector_heads.user_id', auth()->id())->select('sector_heads.sector_id', 'sectors.code')
                ->join('sectors', 'sectors.id', 'sector_heads.sector_id')->get();
        }
        if (in_array(10, $roles)) {
            $departmentsResearch = FacultyResearcher::where('faculty_researchers.user_id', auth()->id())
                ->select('faculty_researchers.college_id', 'colleges.code')
                ->join('colleges', 'colleges.id', 'faculty_researchers.college_id')->get();
        }
        if (in_array(11, $roles)) {
            $departmentsExtension = FacultyExtensionist::where('faculty_extensionists.user_id', auth()->id())
                ->select('faculty_extensionists.college_id', 'colleges.code')
                ->join('colleges', 'colleges.id', 'faculty_extensionists.college_id')->get();
        }
        if (in_array(12, $roles)) {
            $colleges = Associate::where('associates.user_id', auth()->id())->select('associates.college_id', 'colleges.code')
                ->join('colleges', 'colleges.id', 'associates.college_id')->get();
        }
        if (in_array(13, $roles)) {
            $sectors = Associate::where('associates.user_id', auth()->id())->select('associates.sector_id', 'sectors.code')
                ->join('sectors', 'sectors.id', 'associates.sector_id')->get();
        }

        return [
            'roles' => $roles,
            'departments' => $departments,
            'colleges' => $colleges,
            'sectors' => $sectors,
            'departmentsResearch' => $departmentsResearch,
            'departmentsExtension' => $departmentsExtension,
            'quarter' => $quarter,
            'year' => $year
        ];
    }
}
