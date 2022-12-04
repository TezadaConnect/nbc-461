<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Reports\ReportDataController;
use App\Http\Controllers\Maintenances\LockController;
use Illuminate\Http\Request;
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
    Extensionist,
    ExtensionProgram,
    ExtensionProgramDocument,
    FacultyExtensionist,
    FacultyResearcher,
    IntraMobility,
    IntraMobilityDocument,
    Invention,
    InventionDocument,
    Mobility,
    MobilityDocument,
    OtherAccomplishment,
    OtherAccomplishmentDocument,
    OtherDeptAccomplishment,
    OtherDeptAccomplishmentDocument,
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
    ResearchComplete,
    ResearchPresentation,
    ResearchPublication,
    ResearchCopyright,
    Researcher,
    ResearchCitation,
    ResearchDocument,
    ResearchUtilization,
    SectorHead,
    SharedAccomplishment,
    StudentAward,
    StudentAwardDocument,
    StudentTraining,
    StudentTrainingDocument,
    Syllabus,
    SyllabusDocument,
    TechnicalExtension,
    TechnicalExtensionDocument,
    ViableProject,
    ViableProjectDocument,
    Authentication\UserRole,
    Maintenance\College,
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

    /**
     * =============================================================================================
     * 
     * This method checks the conditions/restrictions before performing the submit.
     * 
     * @param Int $report_category_id this paramenter is the ID of report/form category.
     * 
     * @param Int $accomplishment_id this parameter is the primary ID of record.
     * 
     * @return message.
     * 
     * =============================================================================================
     */
    public function check($report_category_id, $accomplishment_id)
    {
        $currentQuarterYear = Quarter::find(1);
        if (LockController::isLocked($accomplishment_id, $report_category_id))
            return redirect()->back()->with('cannot_access', 'Accomplishment was already submitted!');

        if ($report_category_id != 33) {
            $reportdata = new ReportDataController;
            if (empty($reportdata->getDocuments($report_category_id, $accomplishment_id)))
                return redirect()->back()->with('cannot_access', 'Missing Supporting Documents.');
        }

        $research_id = '*';
        if ($report_category_id <= 7){
            switch($report_category_id){
                case 1:
                    $research_id = $accomplishment_id;
                    $is_registrant = Researcher::where('research_id', $accomplishment_id)->where('user_id', auth()->id())->first()->is_registrant;
                    break;
                case 2:
                    $research_id = ResearchComplete::where('research_id', $accomplishment_id)->first()->research_id;
                    $is_registrant = Researcher::where('research_id', $research_id)->where('user_id', auth()->id())->first()->is_registrant;
                    break;
                case 3:
                    $research_id = ResearchPublication::where('research_id', $accomplishment_id)->first()->research_id;
                    $is_registrant = Researcher::where('research_id', $research_id)->where('user_id', auth()->id())->first()->is_registrant;
                    break;
                case 4:
                    $research_id = ResearchPresentation::where('research_id', $accomplishment_id)->first()->research_id;
                    $is_registrant = Researcher::where('research_id', $research_id)->where('user_id', auth()->id())->first()->is_registrant;
                    break;
                case 5:
                    $research_id = ResearchCitation::find($accomplishment_id)->research_id;
                    $is_registrant = Researcher::where('research_id', $research_id)->where('user_id', auth()->id())->first()->is_registrant;
                    break;
                case 6:
                    $research_id = ResearchUtilization::find($accomplishment_id)->research_id;
                    $is_registrant = Researcher::where('research_id', $research_id)->where('user_id', auth()->id())->first()->is_registrant;
                    break;
                case 7:
                    $research_id = ResearchCopyright::where('research_id', $accomplishment_id)->first()->research_id;
                    $is_registrant = Researcher::where('research_id', $research_id)->where('user_id', auth()->id())->first()->is_registrant;
                    break;
                default: 
            }
        }
        if($report_category_id <= 7){
            if($is_registrant == 0){
                if(Report::where('report_reference_id', $accomplishment_id)
                ->where('report_category_id', $report_category_id)
                ->where('report_quarter', $currentQuarterYear->current_quarter)
                ->where('report_year', $currentQuarterYear->current_year)->doesntExist())
                return redirect()->back()->with('cannot_access', 'Wait for the research registrant who tagged you, to submit the research.');
            }
        } elseif($report_category_id == 12){
            $extension_program_id = $accomplishment_id;
            $is_registrant = Extensionist::where('extension_program_id', $extension_program_id)->where('user_id', auth()->id())->first()->is_registrant;
            if ($is_registrant == 0){
                if(Report::where('report_reference_id', $accomplishment_id)
                ->where('report_category_id', $report_category_id)
                ->where('report_quarter', $currentQuarterYear->current_quarter)
                ->where('report_year', $currentQuarterYear->current_year)->doesntExist())
                return redirect()->back()->with('cannot_access', 'Wait for the registrant who tagged you, to submit the extension.');
            }
        }

        if($this->submitAlternate($report_category_id, $accomplishment_id, $research_id) == 1){
            return $this->returnSuccessMessage($report_category_id);
        } else {
            return redirect()->back()->with(
                'cannot_access',
                'Failed to submit the accomplishment. For chairperson/chief and dean/director, please edit the department of your accomplishment as instructed in the edit form.'
            );
        }
    }
    /**
     * =============================================================================================
     * 
     * This method performs the submit/storing of the submitted record to the reports table and returns the submission status.
     * 
     * @param Int $report_category_id this paramenter is the ID of report/form category.
     * 
     * @param Int $accomplishment_id this parameter is the primary ID of record.
     * 
     * @param Int $research_id this parameter is the primary ID of research. Research ID is needed because research module composed of several forms.
     * 
     * @return Int; 1 if success, 0 if failed.
     * 
     * =============================================================================================
     */

    public function submitAlternate($report_category_id, $accomplishment_id, $research_id){
        $report_controller = new ReportDataController;
        $user_id = auth()->id();
        $currentQuarterYear = Quarter::find(1);

        $report_details = null;
        $reportColumns = null;
        $reportValues = null;
        $failedToSubmit = 0;
        $successToSubmit = 0;
        $report_values_array = [$report_category_id, $accomplishment_id]; // 0 => report_category, 1 => id from accomplishment_tables

        switch($report_values_array[0]){
            case 1: case 2: case 3: case 4: case 5: case 6: case 7:
                $research = Research::join('researchers', 'researchers.research_id', 'research.id')->select('researchers.college_id', 'researchers.department_id', 'researchers.is_registrant', 'research.discipline')->where('researchers.user_id', $user_id)->where('research.id', $research_id)->first();
                $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $research['college_id'])->pluck('employees.type')->all();
                $sector_id = College::where('id', $research->college_id)->pluck('sector_id')->first();
                $reportColumns = collect($report_controller->getColumnDataPerReportCategory($report_values_array[0]));
                if($report_values_array[0] == 5){
                    $reportValues = collect($report_controller->getTableDataPerColumnCategory($report_values_array[0], $report_values_array[1]));
                    $report_documents = $report_controller->getDocuments($report_values_array[0], $report_values_array[1]);
                }
                elseif($report_values_array[0] == 6){
                    $reportValues = collect($report_controller->getTableDataPerColumnCategory($report_values_array[0], $report_values_array[1]));
                    $report_documents = $report_controller->getDocuments($report_values_array[0], $report_values_array[1]);
                }
                elseif(($report_values_array[0] <= 4 || $report_values_array[0] == 7 )){
                    $reportValues = collect($report_controller->getTableDataPerColumnCategory($report_values_array[0], $report_values_array[1]));
                    $report_documents = $report_controller->getDocuments($report_values_array[0], $report_values_array[1]);
                }
                $report_details = array_combine($reportColumns->pluck('column')->toArray(), $reportValues->toArray());

                Report::where('report_reference_id', $report_values_array[1])
                    ->where('report_category_id', $report_values_array[0])
                    ->where('user_id', auth()->id())
                    ->where('report_quarter', $currentQuarterYear->current_quarter)
                    ->where('report_year', $currentQuarterYear->current_year)
                    ->delete();

                $type = $this->employeeType($employeeTypes); //Param array
                $sectorIDs = Sector::pluck('id')->all();
                if ($type == 'a') {
                    if ($research->department_id == $research->college_id) {
                        if (in_array($research->college_id, $sectorIDs)){
                            Report::create([
                                'user_id' =>  $user_id,
                                'sector_id' => $sector_id,
                                'college_id' => $research->college_id,
                                'department_id' => $research->department_id,
                                'format' => $type,
                                'report_category_id' => $report_values_array[0],
                                // 'report_code' => null,
                                'research_cluster_id' => $research->discipline,
                                'report_reference_id' => $report_values_array[1] ?? null,
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
                                'college_id' => $research->college_id,
                                'department_id' => $research->department_id,
                                'format' => $type,
                                'report_category_id' => $report_values_array[0],
                                // 'report_code' => null,
                                'research_cluster_id' => $research->discipline,
                                'report_reference_id' => $report_values_array[1] ?? null,
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
                            'college_id' => $research->college_id,
                            'department_id' => $research->department_id,
                            'format' => $type,
                            'report_category_id' => $report_values_array[0],
                            'research_cluster_id' => $research->discipline,
                            'report_reference_id' => $report_values_array[1] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    }
                } elseif ($type == 'f') {
                    if ($research->department_id == $research->college_id) {
                        if ($research->department_id >= 227 && $research->department_id <= 248) { // If branch
                            Report::create([
                                'user_id' =>  $user_id,
                                'sector_id' => $sector_id,
                                'college_id' => $research->college_id,
                                'department_id' => $research->department_id,
                                'format' => $type,
                                'report_category_id' => $report_values_array[0],
                                'research_cluster_id' => $research->discipline,
                                'report_reference_id' => $report_values_array[1] ?? null,
                                'report_details' => json_encode($report_details),
                                'report_documents' => json_encode($report_documents),
                                'report_date' => date("Y-m-d", time()),
                                'report_quarter' => $currentQuarterYear->current_quarter,
                                'report_year' => $currentQuarterYear->current_year,
                            ]);
                        } else {
                            if ($report_values_array[0] >= 1 && $report_values_array[0] <= 8) {
                                Report::create([
                                    'user_id' =>  $user_id,
                                    'sector_id' => $sector_id,
                                    'college_id' => $research->college_id,
                                    'department_id' => $research->department_id,
                                    'format' => $type,
                                    'report_category_id' => $report_values_array[0],
                                    'research_cluster_id' => $research->discipline,
                                    'report_reference_id' => $report_values_array[1] ?? null,
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
                                    'college_id' => $research->college_id,
                                    'department_id' => $research->department_id,
                                    'format' => $type,
                                    'report_category_id' => $report_values_array[0],
                                    'research_cluster_id' => $research->discipline,
                                    'report_reference_id' => $report_values_array[1] ?? null,
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
                            'college_id' => $research->college_id,
                            'department_id' => $research->department_id,
                            'format' => $type,
                            'report_category_id' => $report_values_array[0],
                            'research_cluster_id' => $research->discipline,
                            'report_reference_id' => $report_values_array[1] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    }
                }
                $successToSubmit++;
                return 1;
            break;
            case 8: case 9: case 10: case 11: case 12: case 13: case 14: case 15: case 16: case 29: case 30: case 31: case 32: case 33: case 34: case 38:
                switch($report_values_array[0]){
                    case 8:
                        $collegeAndDepartment = Invention::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 9:
                        $collegeAndDepartment = ExpertServiceConsultant::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 10:
                        $collegeAndDepartment = ExpertServiceConference::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 11:
                        $collegeAndDepartment = ExpertServiceAcademic::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 12:
                        $collegeAndDepartment = Extensionist::select('college_id', 'department_id')->where('user_id', $user_id)->where('extension_program_id', $report_values_array[1])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 13:
                        $collegeAndDepartment = Partnership::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 14:
                        $collegeAndDepartment = Mobility::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 15:
                        $collegeAndDepartment = Reference::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 16:
                        $collegeAndDepartment = Syllabus::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                    case 16:
                        $collegeAndDepartment = Syllabus::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 29:
                        $collegeAndDepartment = AdminSpecialTask::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 30:
                        $collegeAndDepartment = SpecialTask::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 31:
                        $collegeAndDepartment = SpecialTask::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 32:
                        $collegeAndDepartment = SpecialTask::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 33:
                        $collegeAndDepartment = AttendanceFunction::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 34:
                        $collegeAndDepartment = IntraMobility::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 38:
                        $collegeAndDepartment = OtherAccomplishment::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $employeeTypes = Employee::where('user_id', auth()->id())->where('college_id', $collegeAndDepartment['college_id'])->pluck('employees.type')->all();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                }
                $reportColumns = collect($report_controller->getColumnDataPerReportCategory($report_values_array[0]));
                $reportValues = collect($report_controller->getTableDataPerColumnCategory($report_values_array[0], $report_values_array[1]));
                $report_documents = $report_controller->getDocuments($report_values_array[0], $report_values_array[1]);
                $report_details = array_combine($reportColumns->pluck('column')->toArray(), $reportValues->toArray());
                Report::where('report_reference_id', $report_values_array[0])
                    ->where('report_code', $report_values_array[0])
                    ->where('report_category_id', $report_values_array[0])
                    ->where('user_id', auth()->id())
                    ->where('report_quarter', $currentQuarterYear->current_quarter)
                    ->where('report_year', $currentQuarterYear->current_year)
                    ->delete();
                $type = $this->employeeType($employeeTypes);
                if ($type == 'a') {
                    if ($collegeAndDepartment->department_id == $collegeAndDepartment->college_id) {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id,
                            'college_id' => $collegeAndDepartment->college_id,
                            'department_id' => $collegeAndDepartment->department_id,
                            'format' => $type,
                            'report_category_id' => $report_values_array[0],
                            'report_code' => $report_values_array[0] ?? null,
                            'report_reference_id' => $report_values_array[1] ?? null,
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
                            'college_id' => $collegeAndDepartment->college_id,
                            'department_id' => $collegeAndDepartment->department_id,
                            'format' => $type,
                            'report_category_id' => $report_values_array[0],
                            'report_code' => $report_values_array[0] ?? null,
                            'report_reference_id' => $report_values_array[1] ?? null,
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
                                'report_category_id' => $report_values_array[0],
                                'report_code' => $report_values_array[0] ?? null,
                                'report_reference_id' => $report_values_array[1] ?? null,
                                'report_details' => json_encode($report_details),
                                'report_documents' => json_encode($report_documents),
                                'report_date' => date("Y-m-d", time()),
                                'report_quarter' => $currentQuarterYear->current_quarter,
                                'report_year' => $currentQuarterYear->current_year,
                            ]);
                        } else {
                            if (($report_values_array[0] >= 1 && $report_values_array[0] <= 8) || ($report_values_array[0] >= 12 && $report_values_array[0] <= 14) || ($report_values_array[0] >= 34 && $report_values_array[0] <= 37) || $report_values_array[0] == 22 || $report_values_array[0] == 23) {
                                Report::create([
                                    'user_id' =>  $user_id,
                                    'sector_id' => $sector_id,
                                    'college_id' => $collegeAndDepartment->college_id,
                                    'department_id' => $collegeAndDepartment->department_id,
                                    'format' => $type,
                                    'report_category_id' => $report_values_array[0],
                                    'report_code' => $report_values_array[1] ?? null,
                                    'report_reference_id' => $report_values_array[1] ?? null,
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
                                    'report_category_id' => $report_values_array[0],
                                    'report_code' => $report_values_array[1] ?? null,
                                    'report_reference_id' => $report_values_array[1] ?? null,
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
                            'report_category_id' => $report_values_array[0],
                            'report_code' => $report_values_array[1] ?? null,
                            'report_reference_id' => $report_values_array[1] ?? null,
                            'report_details' => json_encode($report_details),
                            'report_documents' => json_encode($report_documents),
                            'report_date' => date("Y-m-d", time()),
                            'report_quarter' => $currentQuarterYear->current_quarter,
                            'report_year' => $currentQuarterYear->current_year,
                        ]);
                    }
                }
                $successToSubmit++;
                return 1;
                break;
            case 17:
            case 18:
            case 19:
            case 20:
            case 21:
            case 22:
            case 23:
            case 35:
            case 36:
            case 37:
            case 39:
                //role and department/ college id
                $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();
                $department_id = '';
                $college_id = '';
                $sector_id = '';
                switch($report_values_array[0]){
                    case 17:
                        $collegeAndDepartment = RequestModel::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 18:
                        $collegeAndDepartment = StudentAward::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 19:
                        $collegeAndDepartment = StudentTraining::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 20:
                        $collegeAndDepartment = ViableProject::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 21:
                        $collegeAndDepartment = CollegeDepartmentAward::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 22:
                        $collegeAndDepartment = OutreachProgram::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 23:
                        $collegeAndDepartment = TechnicalExtension::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 35:
                        $collegeAndDepartment = Mobility::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 36:
                        $collegeAndDepartment = IntraMobility::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 37:
                        $collegeAndDepartment = CommunityEngagement::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                    case 39:
                        $collegeAndDepartment = OtherDeptAccomplishment::select('college_id', 'department_id')->where('user_id', $user_id)->where('id', $report_values_array[0])->first();
                        $sector_id = College::where('id', $collegeAndDepartment->college_id)->pluck('sector_id')->first();
                        break;
                }
                $reportColumns = collect($report_controller->getColumnDataPerReportCategory($report_values_array[0]));
                $reportValues = collect($report_controller->getTableDataPerColumnCategory($report_values_array[0], $report_values_array[1]));
                $report_documents = $report_controller->getDocuments($report_values_array[0], $report_values_array[0]);
                $report_details = array_combine($reportColumns->pluck('column')->toArray(), $reportValues->toArray());
                // dd($report_details);
                if (in_array(5, $roles) && $collegeAndDepartment->department_id != 0) {

                    Report::where('report_reference_id', $report_values_array[0])
                        ->where('report_code', $report_values_array[0])
                        ->where('report_category_id', $report_values_array[0])
                        ->where('user_id', auth()->id())
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)
                        ->delete();
                    if ($report_values_array[0] <= 21 && $report_values_array[0] >= 17) {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id,
                            'college_id' => $collegeAndDepartment->college_id ?? null,
                            'department_id' => $collegeAndDepartment->department_id ?? null,
                            'format' => 'x',
                            'report_category_id' => $report_values_array[0],
                            'report_code' => $report_values_array[1] ?? null,
                            'report_reference_id' => $report_values_array[1] ?? null,
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
                            'report_category_id' => $report_values_array[0],
                            'report_code' => $report_values_array[1] ?? null,
                            'report_reference_id' => $report_values_array[1] ?? null,
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
                    Report::where('report_reference_id', $report_values_array[0])
                        ->where('report_code', $report_values_array[0])
                        ->where('report_category_id', $report_values_array[0])
                        ->where('user_id', auth()->id())
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->where('report_year', $currentQuarterYear->current_year)
                        ->delete();
                    if ($report_values_array[0] <= 21 && $report_values_array[0] >= 17) {
                        Report::create([
                            'user_id' =>  $user_id,
                            'sector_id' => $sector_id ?? null,
                            'college_id' => $collegeAndDepartment->college_id ?? null,
                            'department_id' => $collegeAndDepartment->department_id ?? null,
                            'format' => 'x',
                            'report_category_id' => $report_values_array[0],
                            'report_code' => $report_values_array[1] ?? null,
                            'report_reference_id' => $report_values_array[1] ?? null,
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
                            'report_category_id' => $report_values_array[0],
                            'report_code' => $report_values_array[1] ?? null,
                            'report_reference_id' => $report_values_array[1] ?? null,
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

    /**
     * =============================================================================================
     * 
     * Method that redirects and returns session message per submission.
     * 
     * @param Int $category requires report category code to find the right submition message
     *  
     * @return Session message and redirect to the same page
     * 
     * =============================================================================================
     */
    private function returnSuccessMessage($category)
    { // <<----------- Report category id
        switch ($category) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:  // Researchs
                return redirect()->back()->with('submit_success', 'Accomplisment has been endorsed to your RESEARCH COORDINATOR/DIRECTOR for validation.');
            case 12:
            case 13:
            case 14:
            case 23:
            case 34:
            case 35:
            case 36:
            case 37: // Extesnsions
                return redirect()->back()->with('submit_success', 'Accomplisment has been endorsed to your EXTENSION COORDINATOR/DIRECTOR for validation.');
            default: // Others
                return redirect()->back()->with('submit_success', 'Accomplisment has been endorsed to your CHAIR/CHIEF for validation.');
        }
    }

    /**
     * =============================================================================================
     * 
     * Method that returns the employee type code.
     * 
     * @param Array $employeeRecord requires report category code to find the right submition message
     *  
     * @return String $type. 
     * 
     * =============================================================================================
     */
    public function employeeType($employeeTypes){
        $type = '';
        if (count($employeeTypes) == 2) {
            $getUserTypeFromSession = session()->get('user_type');
            if ($getUserTypeFromSession == 'Faculty Employee') $type = 'f';
            elseif ($getUserTypeFromSession == 'Admin Employee') $type = 'a';
        } elseif (count($employeeTypes) == 1) {
            if ($employeeTypes[0] == 'F') $type = 'f';
            elseif ($employeeTypes[0] == 'A') $type = 'a';
        }
        return $type;
    }
}