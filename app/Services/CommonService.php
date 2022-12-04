<?php
// =============================================================================================
// TITLE: COMMON SERVICE SERVICE
// DESCRIPTION: USED FOR HANDLING REPETATIVE FUNCTION IN THE PROGRAM
// DEVELOPER: TERRENCE CALZADA
// DATE: OCTOBER 16, 2022
// =============================================================================================

namespace App\Services;

use Exception;
use App\Models\Dean;
use App\Models\User;
use App\Models\Report;
use App\Models\Research;
use App\Models\Associate;
use App\Models\DenyReason;
use App\Models\Researcher;
use App\Models\SectorHead;
use App\Models\Chairperson;
use App\Models\ResearchTag;
use App\Helpers\LogActivity;
use App\Models\Extensionist;
use App\Models\ExtensionTag;
use App\Models\TemporaryFile;
use App\Models\ResearchInvite;
use App\Models\ExtensionInvite;
use App\Models\ExtensionProgram;
use App\Models\FacultyResearcher;
use App\Models\FacultyExtensionist;
use App\Models\Maintenance\College;
use App\Models\Maintenance\Department;
use Illuminate\Support\Facades\Storage;
use App\Models\FormBuilder\DropdownOption;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResearchTagNotification;
use App\Http\Controllers\StorageFileController;
use App\Notifications\ExtensionTagNotification;
use App\Notifications\ResearchInviteNotification;
use App\Http\Controllers\Maintenances\LockController;
use App\Http\Controllers\Reports\ReportDataController;

class CommonService
{

    private $storageFileController;

    private $approvalHolderArr = [
        'researcher_approval',
        'extensionist_approval',
        'chairperson_approval',
        'dean_approval',
        'sector_approval',
        'ipqmso_approval'
    ];

    public function __construct(StorageFileController $storageFileController)
    {
        $this->storageFileController = $storageFileController;
    }

    /**
     * =============================================================================================
     * 
     * File upload handler function that returns a string
     * 
     * @param File $file this paramenter is the file itself or input request
     * 
     * @param Input $description this parameter is the description input request.
     * 
     * @param String $additiveName this parameter is added to the filename and is user define.
     * 
     * @param String $route this parameter is used to redirect in other page with an error message.
     * 
     * @return String the file name of the uploaded file.
     * 
     * =============================================================================================
     */
    public function fileUploadHandler($file, $description, $additiveName, $route)
    {
        $fileDesc = $description == "" ? "" : $this->storageFileController->abbrev($description);
        $fileName = "";
        try {
            $fileName = $additiveName . $fileDesc . '-' . now()->timestamp . uniqid() . '.' .  $file->extension();
            $file->storeAs('documents', $fileName, 'local');
            return $fileName;
        } catch (\Throwable $error) {
            return redirect()->route($route)->with('error', 'Unable to upload the file/s, please try again later.');
        }
    }

    /**
     * =============================================================================================
     * 
     * File upload handler for external database function that returns an object or an associative array. 
     * 
     * @param Request $request this paramenter is the whole request input.
     * 
     * @param String $requestName this parameter is the request name.
     * 
     * @param String $description this parameter is the description of file.
     * 
     * @return Object with key value pair; $isError, $imagedata, $memeType, $description and $message.
     * 
     * =============================================================================================
     */
    public function fileUploadHandlerForExternal($request, $requestName, $description = null)
    {
        try {
            if ($request->has($requestName)) {
                $datastring = file_get_contents($request->file([$requestName]));
                $mimetype = $request->file($requestName)->getMimeType();
                $imagedata = unpack("H*hex", $datastring);
                $imagedata = '0x' . strtoupper($imagedata['hex']);
                return [
                    'isError' => false,
                    'image' => $imagedata,
                    'description' => $description,
                    'mimetype' => $mimetype,
                ];
            } else {
                return [
                    'isError' => false,
                    'image' => null,
                    'description' => null,
                    'mimetype' => null,
                ];
            }
        } catch (Exception $error) {
            return [
                'isError' => true,
                'image' => null,
                'description' => null,
                'mimetype' => null,
                'message' => $error->getMessage()
            ];
        }
    }

    public function imageCheckerWithResponseMsg($type, $imagesRecord = [], $request)
    {
        if ($type == 0) {
            if (!$request->has('document')) return true;
        }

        if ($type == 1) {
            if (!$request->has('document')) {
                if (count($imagesRecord) == 0) return true;
            }
        }

        return false;
    }

    /**
     * =============================================================================================
     * 
     * Get submission status function that returns an object or an associative array. 
     * 
     * @param Int $reportCategoryId this paramenter is the ID of report/form category.
     * 
     * @param Int $primaryId this parameter is the primary ID of record.
     * 
     * @return Object with key value pair; $submissionStatus and $submitRole.
     * 
     * =============================================================================================
     */

    public function getSubmissionStatus($primaryId, $reportCategoryId)
    {
        $submissionStatus = array();
        $submitRole = array();
        $submitRole[$primaryId] = 0;
        $reportdata = new ReportDataController;
        if (LockController::isLocked($primaryId, $reportCategoryId)) {
            $submissionStatus[$reportCategoryId][$primaryId] = 1;
            $submitRole[$primaryId] = ReportDataController::getSubmitRole($primaryId, $reportCategoryId);
        } else
            $submissionStatus[$reportCategoryId][$primaryId] = 0;
        if (empty($reportdata->getDocuments($reportCategoryId, $primaryId)))
            $submissionStatus[$reportCategoryId][$primaryId] = 2;

        if ($submissionStatus[$reportCategoryId][$primaryId] == null)
            $submissionStatus[$reportCategoryId][$primaryId] = null;
        return [
            'submissionStatus' => $submissionStatus[$reportCategoryId][$primaryId],
            'submitRole' => $submitRole[$primaryId]
        ];
    }

    /**
     * =============================================================================================
     * 
     * A function that gets all the dropdown values by passing the input field names and the values of each field.
     * 
     * @param Object $formFields this paramenter contains the of fields of a form.
     * 
     * @param Object $formValues this parameter contains the record values.
     * 
     * @return Array $formValues.
     * 
     * =============================================================================================
     */
    public function getDropdownValues($formFields, $formValues)
    {
        foreach ($formFields as $field) {
            if ($field->field_type_name == "dropdown") {
                $dropdownOptions = DropdownOption::where('id', $formValues[$field->name])->where('is_active', 1)->pluck('name')->first();
                if ($dropdownOptions == null)
                    $dropdownOptions = "-";
                $formValues[$field->name] = $dropdownOptions;
            } elseif ($field->field_type_name == "college") {
                if ($formValues[$field->name] == '0') {
                    $formValues[$field->name] = 'N/A';
                } else {
                    $college = College::where('id', $formValues[$field->name])->pluck('name')->first();
                    $formValues[$field->name] = $college;
                }
            } elseif ($field->field_type_name == "department") {
                if ($formValues[$field->name] == '0') {
                    $formValues[$field->name] = 'N/A';
                } else {
                    $department = Department::where('id', $formValues[$field->name])->pluck('name')->first();
                    $formValues[$field->name] = $department;
                }
            }
        }

        return $formValues;
    }

    /**
     * =============================================================================================
     * 
     * A function that gets all the assigned office/cluster by passing the roles of a user.
     * 
     * @param Array $roles this parameter contains all the roles available to a user.
     * 
     * @return Array with key-value pair; $assignment.
     * 
     * =============================================================================================
     */
    public function getAssignmentsByCurrentRoles($roles)
    {
        $assignment = array();
        $assignment[5] = array();
        $assignment[6] = array();
        $assignment[7] = array();
        $assignment[10] = array();
        $assignment[11] = array();
        $assignment[12] = array();
        $assignment[13] = array();

        if (in_array(5, $roles)) {
            $assignment[5] = Chairperson::where('chairpeople.user_id', auth()->id())->select('chairpeople.department_id', 'departments.code')
                ->join('departments', 'departments.id', 'chairpeople.department_id')->get();
        }
        if (in_array(6, $roles)) {
            $assignment[6] = Dean::where('deans.user_id', auth()->id())->select('deans.college_id', 'colleges.code')
                ->join('colleges', 'colleges.id', 'deans.college_id')->get();
        }
        if (in_array(7, $roles)) {
            $assignment[7] = SectorHead::where('sector_heads.user_id', auth()->id())->select('sector_heads.sector_id', 'sectors.code')
                ->join('sectors', 'sectors.id', 'sector_heads.sector_id')->get();
        }
        if (in_array(10, $roles)) {
            // $assignment[10] = FacultyResearcher::where('faculty_researchers.user_id', auth()->id())->join('colleges', 'colleges.id', 'faculty_researchers.college_id')->get();
            $assignment[10] = FacultyResearcher::where('faculty_researchers.user_id', auth()->id())->join('dropdown_options', 'dropdown_options.id', 'faculty_researchers.cluster_id')->get();
        }
        if (in_array(11, $roles)) {
            $assignment[11] = FacultyExtensionist::where('faculty_extensionists.user_id', auth()->id())
                ->select('faculty_extensionists.college_id', 'colleges.code')
                ->join('colleges', 'colleges.id', 'faculty_extensionists.college_id')->get();
        }
        if (in_array(12, $roles)) {
            $assignment[12] = Associate::where('associates.user_id', auth()->id())->select('associates.college_id', 'colleges.code')
                ->join('colleges', 'colleges.id', 'associates.college_id')->get();
        }
        if (in_array(13, $roles)) {
            $assignment[13] = Associate::where('associates.user_id', auth()->id())->select('associates.sector_id', 'sectors.code')
                ->join('sectors', 'sectors.id', 'associates.sector_id')->get();
        }

        return $assignment;
    }

    /**
     * =============================================================================================
     * 
     * A function that gets all the full names of users; usually used in tagging
     * 
     * @return Array with key-value pair; $allUsers.
     * 
     * =============================================================================================
     */
    public function getAllUserNames()
    {
        $allUsers = [];
        $users = User::all()->except(auth()->id());
        $i = 0;
        foreach ($users as $user) {
            if ($user->middle_name != null) {
                $userFullName = $user->last_name . ', ' . $user->first_name . ' ' . substr($user->middle_name, 0, 1) . '.';
                if ($user->suffix != null)
                    $userFullName = $user->last_name . ', ' . $user->first_name . ' ' . substr($user->middle_name, 0, 1) . '. ' . $user->suffix;
            } else {
                if ($user->suffix != null)
                    $userFullName = $user->last_name . ', ' . $user->first_name . ' ' . $user->suffix;
                else
                    $userFullName = $user->last_name . ', ' . $user->first_name;
            }
            $allUsers[$i++] = array("id" => $user->id, 'fullname' => $userFullName);
        }

        return $allUsers;
    }

    /**
     * =============================================================================================
     * 
     * A function that adds the tagged users as researchers/co-extensionists;
     * 
     * @param Array $collaborators this parameter contains the user IDs of tagged users in the form.
     * 
     * @param Int $id this parameter is the record id.
     * 
     * @param String $formName can have a possible value: 'research' or 'extension'.
     * =============================================================================================
     */
    public function addTaggedUsers($collaborators, $id, $formName)
    {
        $count = 0;
        if ($formName == "research") {
            if ($collaborators != null) {
                foreach ($collaborators as $collab) {
                    if ($collab != auth()->id() && ResearchTag::where('research_id', $id)->where('user_id', $collab)->doesntExist()) {
                        ResearchTag::create([
                            'user_id' => $collab,
                            'sender_id' => auth()->id(),
                            'research_id' => $id
                        ]);

                        $researcher = Research::find($id)->researchers;
                        $researcherExploded = explode("/", $researcher);
                        $user = User::find($collab);
                        if ($user->middle_name != '') {
                            array_push($researcherExploded, $user->last_name . ', ' . $user->first_name . ' ' . substr($user->middle_name, 0, 1) . '.');
                        } else {
                            array_push($researcherExploded, $user->last_name . ', ' . $user->first_name);
                        }

                        $research_title = Research::where('id', $id)->pluck('title')->first();
                        $sender = User::where('id', auth()->id())
                                        ->select('users.first_name', 'users.last_name', 'users.middle_name', 'users.suffix')->first();
                        $url_accept = route('research.invite.confirm', $id);
                        $url_deny = route('research.invite.cancel', $id);

                        $notificationData = [
                            'receiver' => $user->first_name,
                            'title' => $research_title,
                            'sender' => $sender->first_name . ' ' . $sender->middle_name . ' ' . $sender->last_name . ' ' . $sender->suffix,
                            'url_accept' => $url_accept,
                            'url_deny' => $url_deny,
                            'date' => date('F j, Y, g:i a'),
                            'type' => 'res-invite'
                        ];

                        Notification::send($user, new ResearchTagNotification($notificationData));
                        Research::where('id', $id)->update([
                            'researchers' => implode("/", $researcherExploded),
                        ]);
                    }
                }
                // LogActivity::addToLog('Had added a co-researcher in the research "'.$research_title.'".'); 
            }
        } else {
            $count = 0;
            $loggedInUser = User::find(auth()->id());
            ExtensionProgram::where('id', $id)->update([
                'extensionists' => $loggedInUser->last_name . ', ' . $loggedInUser->first_name . ' ' . substr($loggedInUser->middle_name, 0, 1) . '.'
            ]);
            if ($collaborators != null) {
                foreach ($collaborators as $collab) {
                    $extensionist = ExtensionProgram::find($id)->extensionists;
                    $extensionistsExploded = explode("/", $extensionist);
                    $user = User::find($collab);
                    if ($user->middle_name != '')
                        array_push($extensionistsExploded, $user->last_name . ', ' . $user->first_name . ' ' . substr($user->middle_name, 0, 1) . '.');
                    else
                        array_push($extensionistsExploded, $user->last_name . ', ' . $user->first_name);

                    ExtensionTag::create([
                        'extension_program_id' => $id,
                        'user_id' => $collab,
                        'sender_id' => auth()->id(),
                    ]);
                    $user = User::find($collab);
                    $extension_title = "Extension Program/Project/Activity";
                    $sender = User::where('id', auth()->id())
                                    ->select('users.first_name', 'users.last_name', 'users.middle_name', 'users.suffix')->first();
                    $url_accept = route('extension.invite.confirm', $id);
                    $url_deny = route('extension.invite.cancel', $id);

                    $notificationData = [
                        'receiver' => $user->first_name,
                        'title' => $extension_title,
                        'sender' => $sender->first_name . ' ' . $sender->middle_name . ' ' . $sender->last_name . ' ' . $sender->suffix,
                        'url_accept' => $url_accept,
                        'url_deny' => $url_deny,
                        'date' => date('F j, Y, g:i a'),
                        'type' => 'ext-invite'
                    ];
                    Notification::send($user, new ExtensionTagNotification($notificationData));

                    ExtensionProgram::where('id', $id)->update([ 'extensionists' => implode("/", $extensionistsExploded),]);
                    $count++;
                }
                LogActivity::addToLog('Had added ' . $count . ' extension partners in an extension program/project/activity.');
            }
        }
    }


    /**
     * =============================================================================================
     * 
     * A function that returns the report to the submitter.
     * 
     * @param Int $reportID this parameter contains the primary ID of the submitted report.
     * 
     * @param String $reviewerPosition this parameter is the position of the reviewer who returns the report.
     * 
     * @param String $remarks is the note or reason for return.
     * =============================================================================================
     */
    public function returnReport($reportID, $reviewerPosition, $remarks)
    {
        if ($reviewerPosition == 'chairperson') {
            DenyReason::create([
                'report_id' => $reportID,
                'user_id' => auth()->id(),
                'position_name' => $reviewerPosition,
                'reason' => $remarks,
            ]);
            Report::where('id', $reportID)->update([
                'chairperson_approval' => 0
            ]);
        }
    }

    /**
     * =============================================================================================
     * 
     * A function that returns an array of pending reports based on the approval role.
     * 
     * @param Array $data is an array/collection of reports.
     * 
     * @param String $type is the role approval.
     * =============================================================================================
     */
    public function getStatusOfIPO($data, $type)
    {
        $newListStatus = [];
        foreach ($data as $item) {
            if ($this->reportStatusChecker($item, $type) != null) array_push($newListStatus, $this->reportStatusChecker($item, $type));
        }
        return $newListStatus;
    }


    private function reportStatusChecker($item, $type)  // Check the status of the item and return the item if pending is true otherwise return null || used in getStatusOfIPO method
    {

        $firstBool = $item->report_category_id >= 1 && $item->report_category_id <= 8;
        $secondBool = ($item->report_category_id >= 12 && $item->report_category_id <= 14) || ($item->report_category_id >= 34 && $item->report_category_id <= 37) || $item->report_category_id == 22 || $item->report_category_id == 23;

        if ($type == $this->approvalHolderArr[0] && $item[$this->approvalHolderArr[0]] == null) { // Reasercher
            if ($item->format == 'f' && $item->report_category_id >= 1 && $item->report_category_id <= 8) return $item;
        }

        if ($type == $this->approvalHolderArr[1] && $item[$this->approvalHolderArr[1]] == null) { // extensionist
            if (($item->report_category_id >= 12 && $item->report_category_id <= 14) || ($item->report_category_id >= 34 && $item->report_category_id <= 37) || $item->report_category_id == 22 || $item->report_category_id == 23) {
                if ($item->format == 'f') return $item;
            }
        }

        if ($type == $this->approvalHolderArr[2]) { // Chair/Chief
            if ($item->department_id != $item->college_id) {
                if ($item[$this->approvalHolderArr[2]] === null) {
                    if ($item->format == 'f') {
                        if ($firstBool) return null;
                        if ($secondBool) return null;
                        return $item;
                    }
                    if ($item->format == 'a') return $item;
                }
            }
        }

        if ($type == $this->approvalHolderArr[3]) { // Dean/Director
            if ($item[$this->approvalHolderArr[3]] === null && $item[$this->approvalHolderArr[2]] != 0 && $item[$this->approvalHolderArr[2]] != null) return $item;
        }

        if ($type == $this->approvalHolderArr[4]) { // Sector Head
            if ($item[$this->approvalHolderArr[4]] === null && $item[$this->approvalHolderArr[3]] != 0 && $item[$this->approvalHolderArr[3]] != null) return $item;
        }

        if ($type == $this->approvalHolderArr[5]) { // IPO
            if ($item[$this->approvalHolderArr[5]] === null && $item[$this->approvalHolderArr[4]] != 0 && $item[$this->approvalHolderArr[4]] != null) return $item;
        }

        return null;
    }

    public function getCollegeDepartmentNames($reports){
        //get_department_and_college_name
        $college_names = [];
        $department_names = [];
        $researchReportCategoryIDs = array(1,2,3,4,5,6,7); // Research and extension categories
        $extensionReportCategoryIDs = array(12, 13, 14, 22, 23, 34, 35, 36, 37);
        foreach($reports as $row){
            if (in_array($row->report_category_id, $researchReportCategoryIDs)){
                $temp_college_name = Researcher::where('college_id', $row->college_id)->join('colleges', 'colleges.id', 'researchers.college_id')->select('colleges.name')->first();
                $temp_department_name = Researcher::where('department_id', $row->department_id)->join('departments', 'departments.id', 'researchers.department_id')->select('departments.name')->first();
            } elseif (in_array($row->report_category_id, $extensionReportCategoryIDs)){
                $temp_college_name = Extensionist::where('college_id', $row->college_id)->join('colleges', 'colleges.id', 'extensionists.college_id')->select('colleges.name')->first();
                $temp_department_name = Extensionist::where('department_id', $row->department_id)->join('departments', 'departments.id', 'extensionists.department_id')->select('departments.name')->first();
            } else{
                $temp_college_name = College::select('name')->where('id', $row->college_id)->first();
                $temp_department_name = Department::select('name')->where('id', $row->department_id)->first();
            }
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

        return [
            'college_names' => $college_names,
            'department_names' => $department_names,
        ];
    }
}
