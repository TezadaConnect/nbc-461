<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\{
    Controller,
    Reports\ReportDataController,
    Maintenances\LockController
};
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use App\Models\{
    AttendanceFunction,
    AttendanceFunctionDocument,
    Chairperson,
    CollegeDepartmentAward,
    CollegeDepartmentAwardDocument,
    CommunityEngagement,
    CommunityEngagementDocument,
    Dean,
    Employee,
    ExpertServiceAcademic,
    ExpertServiceAcademicDocument,
    ExpertServiceConference,
    ExpertServiceConferenceDocument,
    ExpertServiceConsultant,
    ExpertServiceConsultantDocument,
    ExtensionService,
    ExtensionServiceDocument,
    FacultyExtensionist,
    FacultyResearcher,
    IntraMobility,
    IntraMobilityDocument,
    Invention,
    InventionDocument,
    LogActivity,
    Mobility,
    MobilityDocument,
    OtherAccomplishment,
    OtherAccomplishmentDepartment,
    OtherDeptAccomplishment,
    OtherDeptAccomplishmentDepartment,
    OutreachProgram,
    OutreachProgramDocument,
    Partnership,
    PartnershipDocument,
    Reference,
    ReferenceDocument,
    Report,
    Request as RequestModel,
    RequestDocument,
    Research,
    ResearchCitation,
    ResearchComplete,
    ResearchCopyright,
    ResearchDocument,
    ResearchPresentation,
    ResearchPublication,
    ResearchUtilization,
    SectorHead,
    StudentAward,
    StudentAwardDocument,
    StudentTraining,
    StudentTrainingDocument,
    Syllabus,
    SyllabusDocument,
    TechnicalExtension,
    TechnicalExtensionDocument,
    TemporaryFile,
    ViableProject,
    ViableProjectDocument,
    Authentication\UserRole,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
    Maintenance\ReportCategory,
    Maintenance\Sector,
    AdminSpecialTask,
    AdminSpecialTaskDocument,
    SpecialTask,
    SpecialTaskDocument,
};
use Exception;

class SubmissionController extends Controller
{
    public function check($report_category_id, $accomplishment_id){
        $currentQuarterYear = Quarter::find(1);
        if(LockController::isLocked($accomplishment_id, $report_category_id))
            return redirect()->back()->with('cannot_access', 'Accomplishment already submitted.');

        if ($report_category_id != 33) {
            $reportdata = new ReportDataController;
            if(empty($reportdata->getDocuments($report_category_id, $accomplishment_id)))
                return redirect()->back()->with('cannot_access', 'Missing Supporting Documents.');
        }

        $research_code = '*';
        $research_id = '*';
        if($report_category_id >= 1 && $report_category_id <= 7){
            $research_nature_of_involvement = Research::find($accomplishment_id)->nature_of_involvement;
            // dd($research_nature_of_involvement);
            
            // if($research_nature_of_involvement != 11 && $research_nature_of_involvement != 224){
                if($report_category_id == 1){
                    $research_code = Research::where('id', $accomplishment_id)->pluck('research_code')->first();
                    $leadsResearch = Research::where('research_code', $research_code)->pluck('id')->first();
                    if($leadsResearch != $accomplishment_id && $leadsResearch != null)
                        if(!(Report::where('report_reference_id', $leadsResearch)
                        ->where('report_code', $research_code)
                        ->where('report_category_id', 1)
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)->exists()))
                        return redirect()->back()->with('cannot_access', 'Wait for your lead researcher to submit the research.');
                }
                if($report_category_id == 2){
                    $research_id = ResearchComplete::where('id', $accomplishment_id)->pluck('research_id')->first();
                    $research_code = ResearchComplete::where('id', $accomplishment_id)->pluck('research_code')->first();
                    $leadsResearch = Research::where('research_code', $research_code)->pluck('id')->first();
                    $ownResearch = Research::where('research_code', $research_code)->where('user_id', auth()->id())->pluck('id')->first();
                    if($leadsResearch != $ownResearch && $leadsResearch != null)
                        if(!(Report::where('report_reference_id', $accomplishment_id)
                        ->where('report_code', $research_code)
                        ->where('report_category_id', 2)
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)->exists()))
                        return redirect()->back()->with('cannot_access', 'Wait for your lead researcher to submit the research.');
                }
                if($report_category_id == 3){
                    $research_id = ResearchPublication::where('id', $accomplishment_id)->pluck('research_id')->first();
                    $research_code = ResearchPublication::where('id', $accomplishment_id)->pluck('research_code')->first();
                    $leadsResearch = Research::where('research_code', $research_code)->pluck('id')->first();
                    $ownResearch = Research::where('research_code', $research_code)->where('user_id', auth()->id())->pluck('id')->first();
                    if($leadsResearch != $ownResearch && $leadsResearch != null)
                        if(!(Report::where('report_reference_id', $accomplishment_id)
                        ->where('report_code', $research_code)
                        ->where('report_category_id', 3)
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)->exists()))
                        return redirect()->back()->with('cannot_access', 'Wait for your lead researcher to submit the research.');
                }
                if($report_category_id == 4){
                    $research_id = ResearchPresentation::where('id', $accomplishment_id)->pluck('research_id')->first();
                    $research_code = ResearchPresentation::where('id', $accomplishment_id)->pluck('research_code')->first();
                    $leadsResearch = Research::where('research_code', $research_code)->pluck('id')->first();
                    $ownResearch = Research::where('research_code', $research_code)->where('user_id', auth()->id())->pluck('id')->first();
                    if($leadsResearch != $ownResearch && $leadsResearch != null)
                        if(!(Report::where('report_reference_id', $accomplishment_id)
                        ->where('report_code', $research_code)
                        ->where('report_category_id', 4)
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)->exists()))
                        return redirect()->back()->with('cannot_access', 'Wait for your lead researcher to submit the research.');
                }
                if($report_category_id == 5){
                    $research_id = ResearchCitation::where('id', $accomplishment_id)->pluck('research_id')->first();
                    $research_code = ResearchCitation::where('id', $accomplishment_id)->pluck('research_code')->first();
                    $leadsResearch = Research::where('research_code', $research_code)->pluck('id')->first();
                    $ownResearch = Research::where('research_code', $research_code)->where('user_id', auth()->id())->pluck('id')->first();
                    if($leadsResearch != $ownResearch && $leadsResearch != null)
                        if(!(Report::where('report_reference_id', $accomplishment_id)
                        ->where('report_code', $research_code)
                        ->where('report_category_id', 5)
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)->exists()))
                        return redirect()->back()->with('cannot_access', 'Wait for your lead researcher to submit the research.');
                }
                if($report_category_id == 6){
                    $research_id = ResearchUtilization::where('id', $accomplishment_id)->pluck('research_id')->first();
                    $research_code = ResearchUtilization::where('id', $accomplishment_id)->pluck('research_code')->first();
                    $leadsResearch = Research::where('research_code', $research_code)->pluck('id')->first();
                    $ownResearch = Research::where('research_code', $research_code)->where('user_id', auth()->id())->pluck('id')->first();
                    if($leadsResearch != $ownResearch && $leadsResearch != null)
                        if(!(Report::where('report_reference_id', $accomplishment_id)
                        ->where('report_code', $research_code)
                        ->where('report_category_id', 6)
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)->exists()))
                        return redirect()->back()->with('cannot_access', 'Wait for your lead researcher to submit the research.');
                }

                if($report_category_id == 7){
                    $research_id = ResearchCopyright::where('id', $accomplishment_id)->pluck('research_id')->first();
                    $research_code = ResearchCopyright::where('id', $accomplishment_id)->pluck('research_code')->first();
                    $leadsResearch = Research::where('research_code', $research_code)->pluck('id')->first();
                    $ownResearch = Research::where('research_code', $research_code)->where('user_id', auth()->id())->pluck('id')->first();
                    if($leadsResearch != $ownResearch && $leadsResearch != null)
                        if(!(Report::where('report_reference_id', $accomplishment_id)
                        ->where('report_code', $research_code)
                        ->where('report_category_id', 7)
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)->exists()))
                        return redirect()->back()->with('cannot_access', 'Wait for your lead researcher to submit the research.');
                }
            // } else {
            //     if($report_category_id == 1){
            //         $research_code = Research::where('id', $accomplishment_id)->pluck('research_code')->first();
            //     }
            //     if($report_category_id == 2){
            //         $research_id = ResearchComplete::where('id', $accomplishment_id)->pluck('research_id')->first();
            //         $research_code = ResearchComplete::where('id', $accomplishment_id)->pluck('research_code')->first();
            //     }
            //     if($report_category_id == 3){
            //         $research_id = ResearchPublication::where('id', $accomplishment_id)->pluck('research_id')->first();
            //         $research_code = ResearchPublication::where('id', $accomplishment_id)->pluck('research_code')->first();
            //     }
            //     if($report_category_id == 4){
            //         $research_id = ResearchPresentation::where('id', $accomplishment_id)->pluck('research_id')->first();
            //         $research_code = ResearchPresentation::where('id', $accomplishment_id)->pluck('research_code')->first();
            //     }
            //     if($report_category_id == 5){
            //         $research_id = ResearchCitation::where('id', $accomplishment_id)->pluck('research_id')->first();
            //         $research_code = ResearchCitation::where('id', $accomplishment_id)->pluck('research_code')->first();
            //     }
            //     if($report_category_id == 6){
            //         $research_id = ResearchUtilization::where('id', $accomplishment_id)->pluck('research_id')->first();
            //         $research_code = ResearchUtilization::where('id', $accomplishment_id)->pluck('research_code')->first();
            //     }

            //     if($report_category_id == 7){
            //         $research_id = ResearchCopyright::where('id', $accomplishment_id)->pluck('research_id')->first();
            //         $research_code = ResearchCopyright::where('id', $accomplishment_id)->pluck('research_code')->first();
            //     }
            // }
        }
        if($this->submitAlternate($report_category_id, $accomplishment_id, $research_code, $research_id) == 1)
            return redirect()->back()->with('success', 'Accomplishment submitted succesfully.');
        else
            return redirect()->back()->with('cannot_access', 'Failed to submit the accomplishment. Make sure you submit in the respective module (Admin/Faculty Accomplishment). For chairperson/chief and dean/director, please edit the department of your accomplishment as instructed in the edit form.');
    }

    public function submitAlternate($report_category_id, $accomplishment_id, $research_code, $research_id){
        $report_controller = new ReportDataController;
        $user_id = auth()->id();
        $currentQuarterYear = Quarter::find(1);
        // $getUserTypeFromSession = session()->get('user_type');
        // $format_type = '';
        // if($getUserTypeFromSession == 'Faculty Employee')
        //     $format_type = 'f';
        // elseif($getUserTypeFromSession == 'Admin Employee')
        //     $format_type = 'a';


        $report_details;
        $reportColumns;
        $reportValues;
        $failedToSubmit = 0;
        $successToSubmit = 0;
        $report_values_array = [$research_code, $report_category_id, $accomplishment_id, $research_id]; // 0 => research_code , 1 => report_category, 2 => id, 3 => research_id
        $sectorIDs = Sector::pluck('id')->all();

        switch($report_values_array[1]){
            case 1: case 2: case 3: case 4: case 5: case 6: case 7:
                if ($report_values_array[1] == 1) {
                    $collegeAndDepartment = Research::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                    $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                    $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                }
                else {
                    $collegeAndDepartment = Research::select('college_id', 'department_id')->where('research_code', $report_values_array[0])->where('user_id', auth()->id())->first();
                    $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                    $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                }
                $reportColumns = collect($report_controller->getColumnDataPerReportCategory($report_values_array[1]));
                if($report_values_array[1] == 5){
                    $reportValues = collect($report_controller->getTableDataPerColumnCategory($report_values_array[1], $report_values_array[3]));
                    $report_documents = $report_controller->getDocuments($report_values_array[1], $report_values_array[2]);
                }
                elseif($report_values_array[1] == 6){
                    $reportValues = collect($report_controller->getTableDataPerColumnCategory($report_values_array[1], $report_values_array[3]));
                    $report_documents = $report_controller->getDocuments($report_values_array[1], $report_values_array[2]);
                }
                elseif(($report_values_array[1] <= 4 || $report_values_array[1] == 7 )){
                    $reportValues = collect($report_controller->getTableDataPerColumnCategory($report_values_array[1], $report_values_array[3]));
                    $report_documents = $report_controller->getDocuments($report_values_array[1], $report_values_array[2]);
                }
                $report_details = array_combine($reportColumns->pluck('column')->toArray(), $reportValues->toArray());

                Report::where('report_reference_id', $report_values_array[2])
                    ->where('report_code', $report_values_array[0])
                    ->where('report_category_id', $report_values_array[1])
                    ->where('user_id', auth()->id())
                    ->where('report_quarter', $currentQuarterYear->current_quarter)
                    ->where('report_year', $currentQuarterYear->current_year)
                    ->delete();
                $type = '';
                if (count($employee) == 2){
                    $getUserTypeFromSession = session()->get('user_type');
                    if($getUserTypeFromSession == 'Faculty Employee')
                        $type = 'f';
                    elseif($getUserTypeFromSession == 'Admin Employee')
                        $type = 'a';
                } elseif (count($employee) == 1) {
                    if ($employee[0]['type'] == 'F')
                        $type = 'f';
                    elseif ($employee[0]['type'] == 'A')
                        $type = 'a';
                }

                if ($type == 'a') {
                    if ($collegeAndDepartment->department_id == $collegeAndDepartment->college_id) {
                        if(in_array($collegeAndDepartment->college_id, $sectorIDs)){
                            Report::create([
                                'user_id' =>  $user_id,
                                'sector_id' => $sector_id,
                                'college_id' => $collegeAndDepartment->college_id,
                                'department_id' => $collegeAndDepartment->department_id,
                                'format' => $type,
                                'report_category_id' => $report_values_array[1],
                                'report_code' => $report_values_array[0] ?? null,
                                'report_reference_id' => $report_values_array[2] ?? null,
                                'report_details' => json_encode($report_details),
                                'report_documents' => json_encode($report_documents),
                                'report_date' => date("Y-m-d", time()),
                                'chairperson_approval' => 1,
                                'dean_approval' => 1,
                                'report_quarter' => $currentQuarterYear->current_quarter,
                                'report_year' => $currentQuarterYear->current_year,
                            ]);
                        } else{
                            Report::create([
                                'user_id' =>  $user_id,
                                'sector_id' => $sector_id,
                                'college_id' => $collegeAndDepartment->college_id,
                                'department_id' => $collegeAndDepartment->department_id,
                                'format' => $type,
                                'report_category_id' => $report_values_array[1],
                                'report_code' => $report_values_array[0] ?? null,
                                'report_reference_id' => $report_values_array[2] ?? null,
                                'report_details' => json_encode($report_details),
                                'report_documents' => json_encode($report_documents),
                                'report_date' => date("Y-m-d", time()),
                                'chairperson_approval' => 1,
                                'report_quarter' => $currentQuarterYear->current_quarter,
                                'report_year' => $currentQuarterYear->current_year,
                            ]);
                        }
                    } else {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id,
                            'college_id' => $collegeAndDepartment->college_id,
                            'department_id' => $collegeAndDepartment->department_id,
                            'format' => $type,
                            'report_category_id' => $report_values_array[1],
                            'report_code' => $report_values_array[0] ?? null,
                            'report_reference_id' => $report_values_array[2] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    }
                } elseif ($type == 'f') {
                    if ($collegeAndDepartment->department_id == $collegeAndDepartment->college_id) {
                        if ($collegeAndDepartment->department_id >= 227 && $collegeAndDepartment->department_id <= 248) { // If branch
                            Report::create([
                                'user_id' =>  $user_id,
                                'sector_id' => $sector_id,
                                'college_id' => $collegeAndDepartment->college_id,
                                'department_id' => $collegeAndDepartment->department_id,
                                'format' => $type,
                                'report_category_id' => $report_values_array[1],
                                'report_code' => $report_values_array[0] ?? null,
                                'report_reference_id' => $report_values_array[2] ?? null,
                                'report_details' => json_encode($report_details),
                                'report_documents' => json_encode($report_documents),
                                'report_date' => date("Y-m-d", time()),
                                'report_quarter' => $currentQuarterYear->current_quarter,
                                'report_year' => $currentQuarterYear->current_year,
                            ]);
                        } else {
                            if ($report_values_array[1] >= 1 && $report_values_array[1] <= 8) {
                                Report::create([
                                    'user_id' =>  $user_id,
                                    'sector_id' => $sector_id,
                                    'college_id' => $collegeAndDepartment->college_id,
                                    'department_id' => $collegeAndDepartment->department_id,
                                    'format' => $type,
                                    'report_category_id' => $report_values_array[1],
                                    'report_code' => $report_values_array[0] ?? null,
                                    'report_reference_id' => $report_values_array[2] ?? null,
                                    'report_details' => json_encode($report_details),
                                    'report_documents' => json_encode($report_documents),
                                    'report_date' => date("Y-m-d", time()),
                                    'report_quarter' => $currentQuarterYear->current_quarter,
                                    'report_year' => $currentQuarterYear->current_year,
                                ]);
                            } else {
                                Report::create([
                                    'user_id' =>  $user_id,
                                    'sector_id' => $sector_id,
                                    'college_id' => $collegeAndDepartment->college_id,
                                    'department_id' => $collegeAndDepartment->department_id,
                                    'format' => $type,
                                    'report_category_id' => $report_values_array[1],
                                    'report_code' => $report_values_array[0] ?? null,
                                    'report_reference_id' => $report_values_array[2] ?? null,
                                    'report_details' => json_encode($report_details),
                                    'report_documents' => json_encode($report_documents),
                                    'report_date' => date("Y-m-d", time()),
                                    'chairperson_approval' => 1,
                                    'report_quarter' => $currentQuarterYear->current_quarter,
                                    'report_year' => $currentQuarterYear->current_year,
                                ]);
                            }
                        }
                    } else {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id,
                            'college_id' => $collegeAndDepartment->college_id,
                            'department_id' => $collegeAndDepartment->department_id,
                            'format' => $type,
                            'report_category_id' => $report_values_array[1],
                            'report_code' => $report_values_array[0] ?? null,
                            'report_reference_id' => $report_values_array[2] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    }
                } else{
                    return 0;
                }
                $successToSubmit++;
                return 1;
            break;
            case 8: case 9: case 10: case 11: case 12: case 13: case 14: case 15: case 16: case 29: case 30: case 31: case 32: case 33: case 34: case 38:
                switch($report_values_array[1]){
                    case 8:
                        $collegeAndDepartment = Invention::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 9:
                        $collegeAndDepartment = ExpertServiceConsultant::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 10:
                        $collegeAndDepartment = ExpertServiceConference::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 11:
                        $collegeAndDepartment = ExpertServiceAcademic::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 12:
                        $collegeAndDepartment = ExtensionService::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 13:
                        $collegeAndDepartment = Partnership::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 14:
                        $collegeAndDepartment = Mobility::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 15:
                        $collegeAndDepartment = Reference::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 16:
                        $collegeAndDepartment = Syllabus::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    case 16:
                        $collegeAndDepartment = Syllabus::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 29:
                        $collegeAndDepartment = AdminSpecialTask::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 30:
                        $collegeAndDepartment = SpecialTask::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 31:
                        $collegeAndDepartment = SpecialTask::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 32:
                        $collegeAndDepartment = SpecialTask::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 33:
                        $collegeAndDepartment = AttendanceFunction::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 34:
                        $collegeAndDepartment = IntraMobility::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 38:
                        $collegeAndDepartment = OtherAccomplishment::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $employee = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->get();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                }
                $reportColumns = collect($report_controller->getColumnDataPerReportCategory($report_values_array[1]));
                $reportValues = collect($report_controller->getTableDataPerColumnCategory($report_values_array[1], $report_values_array[2]));
                $report_documents = $report_controller->getDocuments($report_values_array[1], $report_values_array[2]);
                $report_details = array_combine($reportColumns->pluck('column')->toArray(), $reportValues->toArray());
                Report::where('report_reference_id', $report_values_array[2])
                    ->where('report_code', $report_values_array[0])
                    ->where('report_category_id', $report_values_array[1])
                    ->where('user_id', auth()->id())
                    ->where('report_quarter', $currentQuarterYear->current_quarter)
                    ->where('report_year', $currentQuarterYear->current_year)
                    ->delete();
                $type = '';
                if (count($employee) == 2){
                    $getUserTypeFromSession = session()->get('user_type');
                    if($getUserTypeFromSession == 'Faculty Employee')
                        $type = 'f';
                    elseif($getUserTypeFromSession == 'Admin Employee')
                        $type = 'a';
                } elseif (count($employee) == 1) {
                    if ($employee[0]['type'] == 'F')
                        $type = 'f';
                    elseif ($employee[0]['type'] == 'A')
                        $type = 'a';
                }
                if ($type == 'a') {
                    if ($collegeAndDepartment->department_id == $collegeAndDepartment->college_id) {
                        if(in_array($collegeAndDepartment->college_id, $sectorIDs)){
                            Report::create([
                                'user_id' =>  $user_id,
                                'sector_id' => $sector_id,
                                'college_id' => $collegeAndDepartment->college_id,
                                'department_id' => $collegeAndDepartment->department_id,
                                'format' => $type,
                                'report_category_id' => $report_values_array[1],
                                'report_code' => $report_values_array[0] ?? null,
                                'report_reference_id' => $report_values_array[2] ?? null,
                                'report_details' => json_encode($report_details),
                                'report_documents' => json_encode($report_documents),
                                'report_date' => date("Y-m-d", time()),
                                'chairperson_approval' => 1,
                                'dean_approval' => 1,
                                'report_quarter' => $currentQuarterYear->current_quarter,
                                'report_year' => $currentQuarterYear->current_year,
                            ]);
                        } else{
                            Report::create([
                                'user_id' =>  $user_id,
                                'sector_id' => $sector_id,
                                'college_id' => $collegeAndDepartment->college_id,
                                'department_id' => $collegeAndDepartment->department_id,
                                'format' => $type,
                                'report_category_id' => $report_values_array[1],
                                'report_code' => $report_values_array[0] ?? null,
                                'report_reference_id' => $report_values_array[2] ?? null,
                                'report_details' => json_encode($report_details),
                                'report_documents' => json_encode($report_documents),
                                'report_date' => date("Y-m-d", time()),
                                'chairperson_approval' => 1,
                                'report_quarter' => $currentQuarterYear->current_quarter,
                                'report_year' => $currentQuarterYear->current_year,
                            ]);
                        }
                    } else {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id,
                            'college_id' => $collegeAndDepartment->college_id,
                            'department_id' => $collegeAndDepartment->department_id,
                            'format' => $type,
                            'report_category_id' => $report_values_array[1],
                            'report_code' => $report_values_array[0] ?? null,
                            'report_reference_id' => $report_values_array[2] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    }
                } elseif ($type == 'f') {
                    if ($collegeAndDepartment->department_id == $collegeAndDepartment->college_id) {
                        if ($collegeAndDepartment->department_id >= 227 && $collegeAndDepartment->department_id <= 248) { // If branch
                            Report::create([
                                'user_id' =>  $user_id,
                                'sector_id' => $sector_id,
                                'college_id' => $collegeAndDepartment->college_id,
                                'department_id' => $collegeAndDepartment->department_id,
                                'format' => $type,
                                'report_category_id' => $report_values_array[1],
                                'report_code' => $report_values_array[0] ?? null,
                                'report_reference_id' => $report_values_array[2] ?? null,
                                'report_details' => json_encode($report_details),
                                'report_documents' => json_encode($report_documents),
                                'report_date' => date("Y-m-d", time()),
                                'report_quarter' => $currentQuarterYear->current_quarter,
                                'report_year' => $currentQuarterYear->current_year,
                            ]);
                        } else {
                            if (($report_values_array[1] >= 1 && $report_values_array[1] <= 8) || ($report_values_array[1] >= 12 && $report_values_array[1] <= 14) || ($report_values_array[1] >= 34 && $report_values_array[1] <= 37) || $report_values_array[1] == 22 || $report_values_array[1] == 23) {
                                Report::create([
                                    'user_id' =>  $user_id,
                                    'sector_id' => $sector_id,
                                    'college_id' => $collegeAndDepartment->college_id,
                                    'department_id' => $collegeAndDepartment->department_id,
                                    'format' => $type,
                                    'report_category_id' => $report_values_array[1],
                                    'report_code' => $report_values_array[0] ?? null,
                                    'report_reference_id' => $report_values_array[2] ?? null,
                                    'report_details' => json_encode($report_details),
                                    'report_documents' => json_encode($report_documents),
                                    'report_date' => date("Y-m-d", time()),
                                    'report_quarter' => $currentQuarterYear->current_quarter,
                                    'report_year' => $currentQuarterYear->current_year,
                                ]);
                            } else {
                                Report::create([
                                    'user_id' =>  $user_id,
                                    'sector_id' => $sector_id,
                                    'college_id' => $collegeAndDepartment->college_id,
                                    'department_id' => $collegeAndDepartment->department_id,
                                    'format' => $type,
                                    'report_category_id' => $report_values_array[1],
                                    'report_code' => $report_values_array[0] ?? null,
                                    'report_reference_id' => $report_values_array[2] ?? null,
                                    'report_details' => json_encode($report_details),
                                    'report_documents' => json_encode($report_documents),
                                    'report_date' => date("Y-m-d", time()),
                                    'chairperson_approval' => 1,
                                    'report_quarter' => $currentQuarterYear->current_quarter,
                                    'report_year' => $currentQuarterYear->current_year,
                                ]);
                            }
                        }
                    } else {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id,
                            'college_id' => $collegeAndDepartment->college_id,
                            'department_id' => $collegeAndDepartment->department_id,
                            'format' => $type,
                            'report_category_id' => $report_values_array[1],
                            'report_code' => $report_values_array[0] ?? null,
                            'report_reference_id' => $report_values_array[2] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    }
                } else{
                    return 0;
                }
                $successToSubmit++;
                return 1;
            break;
            case 17: case 18: case 19: case 20: case 21: case 22: case 23: case 35: case 36: case 37: case 39:
                //role and department/ college id
                $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
                $department_id = '';
                $college_id = '';
                $sector_id = '';
                switch($report_values_array[1]){
                    case 17:
                        $collegeAndDepartment = RequestModel::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 18:
                        $collegeAndDepartment = StudentAward::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 19:
                        $collegeAndDepartment = StudentTraining::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 20:
                        $collegeAndDepartment = ViableProject::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 21:
                        $collegeAndDepartment = CollegeDepartmentAward::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 22:
                        $collegeAndDepartment = OutreachProgram::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 23:
                        $collegeAndDepartment = TechnicalExtension::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 35:
                        $collegeAndDepartment = Mobility::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 36:
                        $collegeAndDepartment = IntraMobility::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 37:
                        $collegeAndDepartment = CommunityEngagement::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                    case 39:
                        $collegeAndDepartment = OtherDeptAccomplishment::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[2])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    break;
                }
                $reportColumns = collect($report_controller->getColumnDataPerReportCategory($report_values_array[1]));
                $reportValues = collect($report_controller->getTableDataPerColumnCategory($report_values_array[1], $report_values_array[2]));
                $report_documents = $report_controller->getDocuments($report_values_array[1], $report_values_array[2]);
                $report_details = array_combine($reportColumns->pluck('column')->toArray(), $reportValues->toArray());
                // dd($report_details);
                if(in_array(5, $roles) && $collegeAndDepartment->department_id != 0){

                    Report::where('report_reference_id', $report_values_array[2])
                        ->where('report_code', $report_values_array[0])
                        ->where('report_category_id', $report_values_array[1])
                        ->where('user_id', auth()->id())
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)
                        ->delete();
                    if ($report_values_array[1] <= 21 && $report_values_array[1] >= 17) {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id,
                            'college_id' => $collegeAndDepartment->college_id ?? null,
                            'department_id' => $collegeAndDepartment->department_id ?? null,
                            'format' => 'x',
                            'report_category_id' => $report_values_array[1],
                            'report_code' => $report_values_array[0] ?? null,
                            'report_reference_id' => $report_values_array[2] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'chairperson_approval' => 1,
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    } else {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id,
                            'college_id' => $collegeAndDepartment->college_id ?? null,
                            'department_id' => $collegeAndDepartment->department_id ?? null,
                            'format' => 'x',
                            'report_category_id' => $report_values_array[1],
                            'report_code' => $report_values_array[0] ?? null,
                            'report_reference_id' => $report_values_array[2] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    }

                    $successToSubmit++;
                    return 1;
                }else if(in_array(6, $roles) && $collegeAndDepartment->department_id == 0){

                    Report::where('report_reference_id', $report_values_array[2])
                        ->where('report_code', $report_values_array[0])
                        ->where('report_category_id', $report_values_array[1])
                        ->where('user_id', auth()->id())
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)
                        ->delete();

                    if ($report_values_array[1] <= 21 && $report_values_array[1] >= 17) {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id ?? null,
                            'college_id' => $collegeAndDepartment->college_id ?? null,
                            'department_id' => $collegeAndDepartment->department_id ?? null,
                            'format' => 'x',
                            'report_category_id' => $report_values_array[1],
                            'report_code' => $report_values_array[0] ?? null,
                            'report_reference_id' => $report_values_array[2] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'chairperson_approval' => 1,
                            'dean_approval' => 1,
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    } else {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id ?? null,
                            'college_id' => $collegeAndDepartment->college_id ?? null,
                            'department_id' => $collegeAndDepartment->department_id ?? null,
                            'format' => 'x',
                            'report_category_id' => $report_values_array[1],
                            'report_code' => $report_values_array[0] ?? null,
                            'report_reference_id' => $report_values_array[2] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    }
                    $successToSubmit++;
                    return 1;
                } else {
                    return 0;
                }
            break;
        }
        \LogActivity::addToLog('An accomplishment submitted.');

        return true;
    }
}
