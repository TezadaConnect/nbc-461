<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\IndividualAccomplishmentReportExport;
use App\Exports\DepartmentConsolidatedAccomplishmentReportExport;
use App\Exports\DepartmentLevelConsolidatedExport;
use App\Exports\CollegeConsolidatedAccomplishmentReportExport;
use App\Exports\CollegeLevelConsolidatedExport;
use App\Exports\IPOAccomplishmentReportExport;
use App\Exports\SectorAccomplishmentReportExport;
use App\Exports\ResearchAccomplishmentReportExport;
use App\Models\{
    Chairperson,
    Dean,
    FacultyExtensionist,
    FacultyResearcher,
    Report,
    SectorHead,
    User,
    FormBuilder\DropdownOption,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\GenerateColumn,
    Maintenance\GenerateTable,
    Maintenance\Sector,
};
use Maatwebsite\Excel\Facades\Excel;

class GenerateController extends Controller
{
    public function index($id, Request $request){
        // dd($request->all());
        $data = '';
        $source_type = '';
        if($request->input("type") == "academic"){
            if($request->input("level") == "individual"){
                // $source_type = "individual";
                if (in_array($request->generatePerson, ['ipo', 'vp', 'dean/director', 'chair/chief'])){
                    $data = User::where('id', $request->employee)->select('users.last_name as name')->first();
                } else
                    $data = User::where('id', $id)->select('users.last_name as name')->first();
            }
            elseif($request->input("level") == "department"){
                // $source_type = "department";
                if (in_array($request->generatePerson, ['ipo', 'vp', 'dean/director'])){
                    $data = Department::where('id', $request->department)->first();
                } else
                    $data = Department::where('id', $id)->first();
            }
            elseif($request->input("level") == "college"){
                // $source_type = "college";
                if (in_array($request->generatePerson, ['ipo', 'vp'])){
                    $data = College::where('id', $request->cbco)->first();
                } else
                    $data = College::where('id', $id)->first();
            }
            elseif($request->input("level") == "sector"){
                if (in_array($request->generatePerson, ['ipo', 'vp']))
                    $data = Sector::where('id', $request->sector)->first();
                else
                    $data = Sector::where('id', $id)->first();
            }
        }
        elseif($request->input("type") == "admin"){
            if($request->input("level") == "individual"){
                if (in_array($request->generatePerson, ['ipo', 'vp', 'dean/director', 'chair/chief'])){
                    $data = User::where('id', $request->employee)->select('users.last_name as name')->first();
                } else
                    $data = User::where('id', $id)->select('users.last_name as name')->first();
            }
            elseif($request->input("level") == "department"){
                if (in_array($request->generatePerson, ['ipo', 'vp', 'dean/director'])){
                    $data = Department::where('id', $request->department)->first();
                } else
                    $data = Department::where('id', $id)->first();
            }
            elseif($request->input("level") == "college"){
                if (in_array($request->generatePerson, ['ipo', 'vp'])){
                    $data = College::where('id', $request->cbco)->first();
                } else
                    $data = College::where('id', $id)->first();

            }
            elseif($request->input("level") == "sector"){
                if (in_array($request->generatePerson, ['ipo', 'vp']))
                    $data = Sector::where('id', $request->sector)->first();
                else
                    $data = Sector::where('id', $id)->first();
            }
        }
        elseif($request->input("type") == "chair_chief"){
            if($request->input("level") == "department_wide"){
                // $source_type = "individual";
                $data = Department::where('id', $request->input("department_id"))->first();
            }
        }
        elseif($request->input("type") == "dean_director"){
            if($request->input("level") == "college_wide"){
                // $source_type = "individual";
                $college_id = Dean::where('deans.user_id', auth()->id())->select('deans.college_id', 'colleges.code')
                        ->join('colleges', 'colleges.id', 'deans.college_id')
                        ->whereNull('deans.deleted_at')->pluck('deans.college_id')->first();
                $data = College::where('id', $request->input("college_id"))->first();
            }
        }

        if($request->input("level") == 'research'){
            $cluster_id = $id;
            $data = DropdownOption::where('id', $cluster_id)->first();
        }

        if($request->input("level") == 'extension'){
            $cluster_id = $id;
            $data = College::where('id', $cluster_id)->first();
        }

        $level = $request->input("level");
        $type = $request->input('type');
        $yearGenerate = $request->input('yearGenerate');
        $quarterGenerate = $request->input('quarterGenerate');
        $quarterGenerate2 = $request->input('quarterGenerate2');
        $college = College::where('id', $request->input('cbco'))->first();
        $fileSuffix = '';

        // dd($level);
        if ($level == "individual") {
            if ($type == "admin" || $type == "academic") {
                $cbco = $request->input('cbco');
                $fileSuffix = strtoupper($request->input("type")).'-QAR-'.$college->code.'-'.$data->name.'-Q'.$quarterGenerate.'-Q'.$quarterGenerate2.'-Y'.$yearGenerate;
                /* */
                $director = Dean::join('users', 'users.id', 'deans.user_id')->where('deans.college_id', $cbco)->first('users.*');
                $getCollege = College::where('colleges.id', $cbco)->first('colleges.*');
                $sectorHead = SectorHead::join('users', 'users.id', 'sector_heads.user_id')->where('sector_heads.sector_id', $getCollege->sector_id)->first('users.*');
                $getSector = Sector::where('id', $getCollege->sector_id)->first();
                return Excel::download(new IndividualAccomplishmentReportExport(
                    $level,
                    $type,
                    $yearGenerate,
                    $quarterGenerate,
                    $quarterGenerate2,
                    $cbco,
                    $id, //userID
                    $getCollege,
                    $getSector,
                    $director,
                    $sectorHead,
                ),
                    $fileSuffix.'.xlsx');
            }
        } 
        elseif ($level == "department_wide") {
            $fileSuffix = 'DEPT-WIDE-QAR-'.$data->code.'-Q'.$request->input('dw_quarter').'-Y'.$request->input('dw_year');
            return Excel::download(new DepartmentLevelConsolidatedExport(
                $level,
                $type,
                $quarterGenerateLevel = $request->input('dw_quarter'),
                $yearGenerateLevel = $request->input('dw_year'),
                $departmentID = $data->id,
                $departmentName = $data->name,
                ),
                $fileSuffix.'.xlsx');
        } elseif ($level == "college_wide") {
            $fileSuffix = 'COLLEGE-WIDE-QAR-'.$data->code.'-Q'.$request->input('cw_quarter').'-'.$request->input('cw_year');
            return Excel::download(new CollegeLevelConsolidatedExport(
                $level,
                $type,
                $quarterGenerateLevel = $request->input('cw_quarter'),
                $yearGenerateLevel = $request->input('cw_year'),
                $collegeID = $data->id,
                $collegeName = $data->name,
                ),
                $fileSuffix.'.xlsx');
        }
        elseif ($level == "department") {
            if ($request->input("type") == "academic")
                $fileSuffix = 'DEPT-QAR-'.$data->code.'-Q'.$quarterGenerate.'-Q'.$quarterGenerate2.'-Y'.$yearGenerate;
            elseif ($request->input("type") == "admin")
                $fileSuffix = 'SECTION-QAR-'.$data->code.'-Q'.$quarterGenerate.'-Q'.$quarterGenerate2.'-Y'.$yearGenerate;
            return Excel::download(new DepartmentConsolidatedAccomplishmentReportExport(
                $level,
                $type,
                $yearGenerate,
                $quarterGenerate,
                $quarterGenerate2,
                $data->id, //DeptID
                $data->name, //DeptName
                ),
                $fileSuffix.'.xlsx');
        }
        elseif ($level == "college") {
            if ($request->input("type") == "academic")
                $fileSuffix = 'COLLEGE-QAR-'.$data->code.'-Q'.$quarterGenerate.'-Q'.$quarterGenerate2.'-Y'.$yearGenerate;
            elseif ($request->input("type") == "admin")
                $fileSuffix = 'OFFICE-QAR-'.$data->code.'-Q'.$quarterGenerate.'-Q'.$quarterGenerate2.'-Y'.$yearGenerate;  
            
            /* */
            return Excel::download(new CollegeConsolidatedAccomplishmentReportExport(
                $level,
                $type,
                $yearGenerate,
                $quarterGenerate,
                $quarterGenerate2,
                $data->id, //collegeID
                $data->name, //collegeName
                ),
                $fileSuffix.'.xlsx');
        }
        elseif($level == "sector"){
            $sector = $data;
            $type = $request->type;
            $asked = 'no one';

            $previousUrl = url()->previous();
            $url = explode('/', $previousUrl);
            if(in_array('sector', $url)){
                $asked = 'sector';
            }
            elseif(in_array('all', $url))
                $asked = 'ipo';

            $fileSuffix = strtoupper($type).'-SECTOR-QAR-'.$data->code.'-Q'.$quarterGenerate.'-Q'.$quarterGenerate2.'-Y'.$yearGenerate;

            return Excel::download(new SectorAccomplishmentReportExport(
                $type,
                $yearGenerate,
                $quarterGenerate,
                $quarterGenerate2,
                $sector,
                $asked,
            ),
            $fileSuffix.'.xlsx');
        }
        elseif($level == 'ipo'){
            $type = $request->type;
            $fileSuffix = 'IPO-LEVEL-QAR-'.strtoupper($type).'-Q'.$quarterGenerate.'-Q'.$quarterGenerate2.'-Y'.$yearGenerate;

            return Excel::download(new IPOAccomplishmentReportExport(
                $type,
                $quarterGenerate,
                $quarterGenerate2,
                $yearGenerate,
            ),
            $fileSuffix.'.xlsx');
        } elseif ($level == 'research'){
            $clusterName = $data->name;
            $level = $request->level;
            $year = $request->year_generate;
            $fileSuffix = 'QAR-RESEARCH-'.strtoupper($clusterName).'-Y'.$year;

            return Excel::download(new ResearchAccomplishmentReportExport(
                $id,
                $clusterName,
                $level,
                $year,
            ),
            $fileSuffix.'.xlsx');
        }

        elseif ($level == 'extension'){
            $clusterName = $data->name;
            $type = $request->type;
            $year = $request->year_generate;
            $fileSuffix = 'QAR-EXTENSION-'.strtoupper($clusterName).'-Y'.$year;

            return Excel::download(new ResearchAccomplishmentReportExport(
                $id,
                $clusterName,
                $type,
                $year,
            ),
            $fileSuffix.'.xlsx');
        }
    }

    public function documentView($reportID){
        $reportDocuments = json_decode(Report::where('id', $reportID)->pluck('report_documents')->first(), true);
        return view('reports.generate.document', compact('reportDocuments'));
    }

    // public function optionalGeneration(Request $request){
    //     if ($request->level == "individual"){
    //         $dept = $request->delivery_unit;
    //             $fileSuffix = strtoupper($request->input("type")).'-QAR-'.$college->code.'-'.$data->name.'-Q'.$quarterGenerate.'-Y'.$yearGenerate;
    //             $departmentIDs = Department::where('college_id', $cbco)->pluck('id')->all();
    //             /* */
    //             $director = Dean::join('users', 'users.id', 'deans.user_id')->where('deans.college_id', $cbco)->first('users.*');
    //             $getCollege = College::where('colleges.id', $cbco)->first('colleges.*');
    //             $sectorHead = SectorHead::join('users', 'users.id', 'sector_heads.user_id')->where('sector_heads.sector_id', $getCollege->sector_id)->first('users.*');
    //             $getSector = Sector::where('id', $getCollege->sector_id)->first();
    //             return Excel::download(new IndividualAccomplishmentReportExport(
    //                 $level,
    //                 $request->type,
    //                 $yearGenerate,
    //                 $quarterGenerate,
    //                 $dept,
    //                 $request->user_id,
    //                 $getCollege,
    //                 $getSector,
    //                 $director,
    //                 $sectorHead,
    //             ),
    //                 $fileSuffix.'.xlsx');
    //     }
    // }
}
