<?php
// =============================================================================================
// TITLE: COMMON SERVICE SERVICE
// DESCRIPTION: USED FOR HANDLING REPETATIVE FUNCTION IN THE PROGRAM
// DEVELOPER: TERRENCE CALZADA
// DATE: OCTOBER 16, $reportCategoryId022
// =============================================================================================

namespace App\Services;

use Exception;
use App\Models\Dean;
use App\Models\User;
use App\Models\Research;
use App\Models\Associate;
use App\Models\SectorHead;
use App\Models\Chairperson;
use App\Helpers\LogActivity;
use App\Models\TemporaryFile;
use App\Models\ResearchInvite;
use App\Models\FacultyResearcher;
use App\Models\FacultyExtensionist;
use App\Models\Maintenance\College;
use App\Models\Maintenance\Department;
use Illuminate\Support\Facades\Storage;
use App\Models\FormBuilder\DropdownOption;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\StorageFileController;
use App\Notifications\ResearchInviteNotification;
use App\Http\Controllers\Maintenances\LockController;
use App\Http\Controllers\Reports\ReportDataController;

class CommonService {

    private $storageFileController;

    public function __construct(StorageFileController $storageFileController){
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
    public function fileUploadHandler($file, $description, $additiveName, $route){
        $fileDesc = $description == "" ? "" : $this->storageFileController->abbrev($description);
        $fileName = "";
        try {
            $fileName = $additiveName . $fileDesc . '-' . now()->timestamp.uniqid() . '.' .  $file->extension();
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
    public function fileUploadHandlerForExternal($request, $requestName, $description = null){
        try {
            if($request->has($requestName)){
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

    public function imageCheckerWithResponseMsg($type, $imagesRecord = [], $request){
        if($type == 0){
            if (!$request->has('document')) return true;
        }

        if($type == 1){
            if(!$request->has('document')){
                if(count($imagesRecord) == 0) return true;
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

    public function getSubmissionStatus($primaryId, $reportCategoryId){
        $submissionStatus = array();
        $submitRole = array();
        $submitRole[$primaryId] = 0;
        $reportdata = new ReportDataController;
            if (LockController::isLocked($primaryId, $reportCategoryId)) {
                $submissionStatus[$reportCategoryId][$primaryId] = 1;
                $submitRole[$primaryId] = ReportDataController::getSubmitRole($primaryId, $reportCategoryId);
            }
            else
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
    public function getDropdownValues($formFields, $formValues) {
        foreach($formFields as $field){
            if($field->field_type_name == "dropdown"){
                $dropdownOptions = DropdownOption::where('id', $formValues[$field->name])->where('is_active', 1)->pluck('name')->first();
                if($dropdownOptions == null)
                    $dropdownOptions = "-";
                $formValues[$field->name] = $dropdownOptions;
            }
            elseif($field->field_type_name == "college"){
                if($formValues[$field->name] == '0'){
                    $formValues[$field->name] = 'N/A';
                }
                else{
                    $college = College::where('id', $formValues[$field->name])->pluck('name')->first();
                    $formValues[$field->name] = $college;
                }
            }
            elseif($field->field_type_name == "department"){
                if($formValues[$field->name] == '0'){
                    $formValues[$field->name] = 'N/A';
                }
                else{
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
    public function getAssignmentsByCurrentRoles($roles){
        $assignment = array();
        $assignment[5] = array();
        $assignment[6] = array();
        $assignment[7] = array();
        $assignment[10] = array();
        $assignment[11] = array();
        $assignment[12] = array();
        $assignment[13] = array();
        
        if(in_array(5, $roles)){
            $assignment[5] = Chairperson::where('chairpeople.user_id', auth()->id())->select('chairpeople.department_id', 'departments.code')
                                        ->join('departments', 'departments.id', 'chairpeople.department_id')->get();
        }
        if(in_array(6, $roles)){
            $assignment[6] = Dean::where('deans.user_id', auth()->id())->select('deans.college_id', 'colleges.code')
                            ->join('colleges', 'colleges.id', 'deans.college_id')->get();
        }
        if(in_array(7, $roles)){
            $assignment[7] = SectorHead::where('sector_heads.user_id', auth()->id())->select('sector_heads.sector_id', 'sectors.code')
                        ->join('sectors', 'sectors.id', 'sector_heads.sector_id')->get();
        }
        if(in_array(10, $roles)){
            $assignment[10] = FacultyResearcher::where('faculty_researchers.user_id', auth()->id())->join('dropdown_options', 'dropdown_options.id', 'faculty_researchers.cluster_id')->get();
        }
        if(in_array(11, $roles)){
            $assignment[11] = FacultyExtensionist::where('faculty_extensionists.user_id', auth()->id())
                                        ->select('faculty_extensionists.college_id', 'colleges.code')
                                        ->join('colleges', 'colleges.id', 'faculty_extensionists.college_id')->get();
        }
        if(in_array(12, $roles)){
            $assignment[12] = Associate::where('associates.user_id', auth()->id())->select('associates.college_id', 'colleges.code')
                            ->join('colleges', 'colleges.id', 'associates.college_id')->get();
        }
        if(in_array(13, $roles)){
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
    public function getAllUserNames(){
        $allUsers = [];
        $users = User::all()->except(auth()->id());
        $i = 0;
        foreach($users as $user) {
            if ($user->middle_name != null) {
                $userFullName = $user->last_name.', '.$user->first_name.' '.substr($user->middle_name, 0, 1).'.';
                if ($user->suffix != null) 
                    $userFullName = $user->last_name.', '.$user->first_name.' '.substr($user->middle_name, 0, 1).'. '.$user->suffix;
            }
            else {
                if ($user->suffix != null)
                    $userFullName = $user->last_name.', '.$user->first_name.' '.$user->suffix;
                else
                    $userFullName = $user->last_name.', '.$user->first_name;
            }
            $allUsers[$i++] = array("id" => $user->id, 'fullname' => $userFullName);
        }

        return $allUsers;
    }

    public function addTaggedUsers($collaborators, $research, $formName){
        $count = 0;
        if ($formName == "research"){
            if ($collaborators != null) {
                foreach ($collaborators as $collab) {
                    if ($collab != auth()->id()) {
                        ResearchInvite::create([
                            'user_id' => $collab,
                            'sender_id' => auth()->id(),
                            'research_id' => $research->id
                        ]);
    
                        $researcher = Research::find($research->id)->researchers;
                        $researcherExploded = explode("/", $researcher);
                        $user = User::find($collab);
                        if ($user->middle_name != '') {
                            array_push($researcherExploded, $user->last_name.', '.$user->first_name.' '.substr($user->middle_name,0,1).'.');
                        } else {
                            array_push($researcherExploded, $user->last_name.', '.$user->first_name);
                        }
                        
                        $research_title = Research::where('id', $research->id)->pluck('title')->first();
                        $sender = User::join('research', 'research.user_id', 'users.id')
                                        ->where('research.user_id', auth()->id())
                                        ->where('research.id', $research->id)
                                        ->select('users.first_name', 'users.last_name', 'users.middle_name', 'users.suffix')->first();
                        $url_accept = route('research.invite.confirm', $research->id);
                        $url_deny = route('research.invite.cancel', $research->id);
    
                        $notificationData = [
                            'receiver' => $user->first_name,
                            'title' => $research_title,
                            'sender' => $sender->first_name.' '.$sender->middle_name.' '.$sender->last_name.' '.$sender->suffix,
                            'url_accept' => $url_accept,
                            'url_deny' => $url_deny,
                            'date' => date('F j, Y, g:i a'),
                            'type' => 'res-invite'
                        ];
    
                        Notification::send($user, new ResearchInviteNotification($notificationData));
                    }
                    $count++;
                    Research::where('id', $research->id)->update([
                        'researchers' => implode("/", $researcherExploded),
                    ]);
                }
                LogActivity::addToLog('Had added a co-researcher in the research "'.$research_title.'".'); 
            }     
        }
    }
}
