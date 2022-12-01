<?php

namespace App\Http\Controllers\ExtensionPrograms;

use App\Helpers\LogActivity;
use App\Http\Controllers\{
    Controller,
    Maintenances\LockController,
    Reports\ReportDataController,
    StorageFileController,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Notification
};
use App\Models\{
    Employee,
    Extensionist,
    ExtensionProgram,
    ExtensionProgramDocument,
    TemporaryFile,
    FormBuilder\DropdownOption,
    FormBuilder\ExtensionProgramField,
    FormBuilder\ExtensionProgramForm,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
    User,
    ExtensionTag,
};
use App\Notifications\ExtensionTagNotification;
use App\Rules\Keyword;
use App\Services\CommonService;
use App\Services\DateContentService;
use Exception;

class ExtensionProgramController extends Controller
{
    protected $storageFileController;
    private $commonService;

    public function __construct(StorageFileController $storageFileController, CommonService $commonService){
        $this->storageFileController = $storageFileController;
        $this->commonService = $commonService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', ExtensionProgram::class);

        $currentQuarterYear = Quarter::find(1);

        $extensionServices = ExtensionProgram::join('extensionists', 'extensionists.extension_program_id', 'extension_programs.id')
                            ->where('extensionists.user_id', auth()->id())
                            ->whereNull('extensionists.deleted_at')
                            ->join('dropdown_options', 'dropdown_options.id', 'extension_programs.status')
                            ->join('colleges', 'colleges.id', 'extensionists.college_id')
                            ->select(DB::raw('extension_programs.*, dropdown_options.name as status, colleges.name as college_name'))
                            ->orderBy('extension_programs.updated_at', 'desc')
                            ->get();
        $tags = ExtensionTag::where('extension_tags.user_id', auth()->id())
            ->where('extension_tags.status', null)
            ->join('extension_programs', 'extension_programs.id', 'extension_tags.extension_program_id')
            ->join('users', 'users.id', 'extension_tags.sender_id')
            ->select(
                'users.first_name', 'users.last_name', 'users.middle_name', 'users.suffix',
                'extension_programs.id', 'extension_tags.extension_program_id',
                'extension_tags.status', 'extension_programs.title_of_extension_program',
                'extension_programs.title_of_extension_project', 'extension_programs.title_of_extension_activity', 
            )->get();

        $submissionStatus = array();
        $submitRole = array();
        $reportdata = new ReportDataController;
        foreach ($extensionServices as $extension) {
            $submissionStatus[12][$extension->id] = $this->commonService->getSubmissionStatus($extension->id, 12)['submissionStatus'];
            $submitRole[$extension->id] = $this->commonService->getSubmissionStatus($extension->id, 12)['submitRole'];
        }
        return view('extension-programs.index', compact('extensionServices', 'currentQuarterYear', 'tags', 'submissionStatus', 'submitRole'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $currentQuarter = Quarter::find(1)->current_quarter;
        $this->authorize('create', ExtensionProgram::class);

        if(ExtensionProgramForm::where('id', 4)->pluck('is_active')->first() == 0)
            return view('inactive');

        $extensionServiceFields = DB::select("CALL get_extension_program_fields_by_form_id(4)");
        $dropdown_options = [];
        foreach($extensionServiceFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        if(session()->get('user_type') == 'Faculty Employee')
            $colleges = Employee::where('user_id', auth()->id())->where('type', 'F')->pluck('college_id')->all();
        else
            $colleges = Employee::where('user_id', auth()->id())->where('type', 'A')->pluck('college_id')->all();

        $departments = Department::whereIn('college_id', $colleges)->get();

        $allUsers = $this->commonService->getAllUserNames();
        return view('extension-programs.create', compact('extensionServiceFields', 'colleges', 'departments', 'dropdown_options', 'allUsers', 'currentQuarter'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', ExtensionProgram::class);
        
        if(ExtensionProgramForm::where('id', 4)->pluck('is_active')->first() == 0)
        return view('inactive');
        
        $currentQuarterYear = Quarter::find(1);
        $value = $request->input('amount_of_funding');
        $value = (float) str_replace(",", "", $value);
        $value = number_format($value,2,'.','');
        $request->merge([
            'amount_of_funding' => $value,
            'report_quarter' => $currentQuarterYear->current_quarter,
            'report_year' => $currentQuarterYear->current_year,
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
        ]);
        $request->validate([
            // 'keywords' => new Keyword,
            'college_id' => 'required',
            'department_id' => 'required'
        ]);
        $data = $request->except(['_token', '_method', 'document', 'extensionists', 'nature_of_involvement', 'college_id', 'department_id']);
        foreach ($data as $key => $value) {
            if ((new DateContentService())->isValidDate($value) == true)
                $request->merge([ $key => (new DateContentService())->checkDateContent($request, $key) ]);
        }
        //Getting the request again after merge
        $input = $request->except(['_token', '_method', 'document', 'extensionists', 'nature_of_involvement', 'college_id', 'department_id']);
        $eService = ExtensionProgram::create($input);
        Extensionist::create([
            'extension_program_id' => $eService->id,
            'department_id' => $request->input('department_id'),
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
            'user_id' => auth()->id(),
            'nature_of_involvement' => $request->input('nature_of_involvement'),
            'is_registrant' => 1,
        ]);
        ExtensionTag::create([
            'extension_program_id' => $eService->id,
            'user_id' => auth()->id(),
            'sender_id' => auth()->id(),
            'status' => 1,
        ]);
        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), 'ES-', 'extension-programs.index');
                if(is_string($fileName)) ExtensionProgramDocument::create(['extension_program_id' => $eService->id, 'filename' => $fileName]);
                else return $fileName;
            }
        }

        $this->commonService->addTaggedUsers($request->input('extensionists'), $eService->id, 'extension');
        LogActivity::addToLog('Had added an extension program/project/activity.');

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(0, null, $request);
        if($imageChecker) return redirect()->route('extension-programs.index')->with('warning', 'Need to attach supporting documents to enable submission');

        return redirect()->route('extension-programs.index')->with('save_success', 'Extension program/project/activity has been added.');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ExtensionProgram $extension_program)
    {
        $this->authorize('view', ExtensionProgram::class);

        if(ExtensionProgramForm::where('id', 4)->pluck('is_active')->first() == 0)
            return view('inactive');

        $extension_program = ExtensionProgram::where('extension_programs.id', $extension_program->id)
                ->join('extensionists', 'extensionists.extension_program_id', 'extension_programs.id')
                ->select('extension_programs.*', 'extensionists.nature_of_involvement', 'extensionists.department_id',
                    'extensionists.college_id')->first();
        $extensionServiceFields = DB::select("CALL get_extension_program_fields_by_form_id(4)");
        $extensionServiceDocuments = ExtensionProgramDocument::where('extension_program_id', $extension_program->id)->get()->toArray();
        $values = $extension_program->toArray();
        $values = $this->commonService->getDropdownValues($extensionServiceFields, $values);

        return view('extension-programs.show', compact('extension_program', 'extensionServiceDocuments', 'values', 'extensionServiceFields'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ExtensionProgram $extension_program)
    {
        $this->authorize('update', ExtensionProgram::class);
        $currentQuarter = Quarter::find(1)->current_quarter;

        if(LockController::isLocked($extension_program->id, 12)){
            return redirect()->back()->with('cannot_access', 'This accomplishment was already submitted. If you wish to edit, you may request to return the accomplishment.');
        }
        if(ExtensionProgramForm::where('id', 4)->pluck('is_active')->first() == 0)
            return view('inactive');
        $extensionServiceFields = DB::select("CALL get_extension_program_fields_by_form_id(4)");

        $dropdown_options = [];
        foreach($extensionServiceFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;
            }
        }
        $extensionServiceDocuments = ExtensionProgramDocument::where('extension_program_id', $extension_program->id)->get()->toArray();

        if(session()->get('user_type') == 'Faculty Employee')
            $colleges = Employee::where('user_id', auth()->id())->where('type', 'F')->pluck('college_id')->all();
        else
            $colleges = Employee::where('user_id', auth()->id())->where('type', 'A')->pluck('college_id')->all();

        $departments = Department::whereIn('college_id', $colleges)->get();

        if ($extension_program->department_id != null)
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(".$extension_program->department_id.")");
        else
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(0)");
            
        $extensionist = Extensionist::where('extension_program_id', $extension_program->id)->where('user_id', auth()->id())->first();
        $value = $extension_program->toArray();
        $value['nature_of_involvement'] = $extensionist->nature_of_involvement;
        $value['department_id'] = $extensionist->department_id;

        $allUsers = $this->commonService->getAllUserNames();
        $taggedUserIDs = ExtensionTag::where('extension_program_id', $extension_program->id)->pluck('user_id')->all();
        if (Extensionist::where('user_id', auth()->id())->where('extension_program_id', $extension_program->id)->first()['is_registrant'] == '1')
            return view('extension-programs.edit', compact('value', 'extensionServiceFields', 'extensionServiceDocuments', 'colleges', 'collegeOfDepartment', 'departments', 'dropdown_options', 'currentQuarter', 'allUsers', 'taggedUserIDs'));

        return view('extension-programs.edit-code', compact('value', 'extensionServiceFields', 'extensionServiceDocuments', 'colleges', 'collegeOfDepartment', 'departments', 'dropdown_options', 'currentQuarter', 'allUsers', 'taggedUserIDs'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExtensionProgram $extension_program)
    {
        $this->authorize('update', ExtensionProgram::class);
        $currentQuarterYear = Quarter::find(1);

        if(ExtensionProgramForm::where('id', 4)->pluck('is_active')->first() == 0)
            return view('inactive');

        $value = $request->input('amount_of_funding');
        $value = (float) str_replace(",", "", $value);
        $value = number_format($value,2,'.','');
        $data = $request->except(['_token', '_method', 'document', 'funding_amount', 'extensionists', 'nature_of_involvement', 'college_id', 'department_id']);
        foreach ($data as $key => $value) {
            if ((new DateContentService())->isValidDate($value) == true)
                $request->merge([ $key => (new DateContentService())->checkDateContent($request, $key) ]);
        }

        $request->merge([
            'amount_of_funding' => $value,
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
            'report_quarter' => $currentQuarterYear->current_quarter,
            'report_year' => $currentQuarterYear->current_year,
        ]);

        if ($request->input('total_no_of_hours') != '') {
            $request->validate([
                'total_no_of_hours' => 'numeric',
            ]);
        }

        $input = $request->except(['_token', '_method', 'document', 'funding_amount', 'extensionists', 'nature_of_involvement', 'college_id', 'department_id']);
        $extension_program->update(['description' => '-clear']);
        $extension_program->update($input);

        Extensionist::where('extension_program_id', $extension_program->id)->where('user_id', auth()->id())
            ->update([
                'college_id' => $request->input('college_id'),
                'department_id' => $request->input('department_id'),
                'nature_of_involvement' => $request->input('nature_of_involvement'),
            ]);

        if(Extensionist::where('user_id', auth()->id())->where('extension_program_id', $extension_program->id)->first()['is_registrant'] == 1){
            $details = $request->except(['_token', '_method', 'document', 'nature_of_involvement', 'college_id', 'department_id']);
            ExtensionProgram::where('id', $extension_program->id)->update($details);
        }

        $this->commonService->updateTaggedCollaborators($request, $extension_program, 'extension');
        $this->commonService->addTaggedUsers($request->input('extensionists'), $extension_program->id, 'extension');
        $taggedUsersID = ExtensionTag::where('extension_program_id', $extension_program->id)->pluck('user_id')->all();
        foreach($taggedUsersID as $taggedID){
            if (!in_array($taggedID, $request->input('extensionists')))
                ExtensionTag::where('extension_program_id', $extension_program->id)->where('user_id', $taggedID)->delete();
        }
        LogActivity::addToLog('Had updated an extension program/project/activity.');
        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), 'ES-', 'extension-programs.index');
                if(is_string($fileName)) ExtensionProgramDocument::create(['extension_program_id' => $extension_program->id, 'filename' => $fileName]);
                else return $fileName;
            }
        }

        $imageRecord = ExtensionProgramDocument::where('extension_program_id', $extension_program->id)->get();
        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(1, $imageRecord, $request);
        if($imageChecker) return redirect()->route('extension-programs.index')->with('warning', 'Need to attach supporting documents to enable submission');

        return redirect()->route('extension-programs.index')->with('save_success', 'Extension program/project/activity has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExtensionProgram $extension_program)
    {
        $this->authorize('delete', ExtensionProgram::class);

        if(LockController::isLocked($extension_program->id, 12)){
            return redirect()->back()->with('cannot_access', 'This accomplishment was already submitted. If you wish to edit, you may request to return the accomplishment.');
        }

        if(ExtensionProgramForm::where('id', 4)->pluck('is_active')->first() == 0)
            return view('inactive');

        if(ExtensionTag::where('extension_program_id', $extension_program->id)->where('user_id', auth()->id())->pluck('is_owner')->first() == '1'){
            $extension_program->delete();
            ExtensionTag::where('extension_program_id', $extension_program->id)->delete();
            ExtensionProgramDocument::where('extension_program_id', $extension_program->id)->delete();
        }
        else{
            $extension_program->delete();
        }

        LogActivity::addToLog('Had deleted an extension program/project/activity.');

        return redirect()->route('extension-programs.index')->with('success', 'Extension program/project/activity has been deleted.');
    }

    public function removeDoc($filename){
        $this->authorize('delete', ExtensionProgram::class);

        ExtensionProgramDocument::where('filename', $filename)->delete();

        LogActivity::addToLog('Had deleted a document of an extension program/project/activity.');

        // Storage::delete('documents/'.$filename);
        return true;
    }

    public function addExtension($extension_program_id, Request $request){
        $currentQuarterYear = Quarter::find(1);
        $currentQuarter = Quarter::find(1)->current_quarter;

        $id = $extension_program_id;

        $this->authorize('create', ExtensionProgram::class);

        if(ExtensionProgramForm::where('id', 4)->pluck('is_active')->first() == 0)
            return view('inactive');

        $extensionServiceFields = DB::select("CALL get_extension_program_fields_by_form_id(4)");

        $dropdown_options = [];
        foreach($extensionServiceFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        if(session()->get('user_type') == 'Faculty Employee')
            $colleges = Employee::where('user_id', auth()->id())->where('type', 'F')->pluck('college_id')->all();
        else
            $colleges = Employee::where('user_id', auth()->id())->where('type', 'A')->pluck('college_id')->all();

        $departments = Department::whereIn('college_id', $colleges)->get();

        $extension_program = ExtensionProgram::where('id', $id)->first();
        $value = $extension_program;
        $value->toArray();
        $value = collect($extension_program);
        $value = $value->except(['nature_of_involvement', 'college_id', 'department_id']);
        $value = $value->toArray();
        $notificationID = $request->get('id');
        $is_owner = 0;
        $extensionServiceDocuments = ExtensionProgramDocument::where('extension_program_id', $extension_program->id)->get();
        if ($extension_program->department_id != null) {
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(".$extension_program->department_id.")");
        }
        else {
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(0)");
        }

        return view('extension-programs.create-code', compact('value', 'extensionServiceFields', 'colleges', 'is_owner', 'notificationID', 'departments', 'collegeOfDepartment', 'extensionServiceDocuments', 'dropdown_options', 'currentQuarter'));
    }


    public function saveExtension($id, Request $request){
        $this->authorize('create', ExtensionProgram::class);

        if(ExtensionProgramForm::where('id', 4)->pluck('is_active')->first() == 0)
            return view('inactive');

        $extensionService = ExtensionProgram::where('id', $id)->first();

        if ($extensionService == null)
            return redirect()->route('extension-programs.index')->with('cannot_access', 'Extension program/project/activity not found in the system.');

        Extensionist::create([
            'extension_program_id' => $id,
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
            'department_id' => $request->input('department_id'),
            'user_id' => auth()->id(),
            'nature_of_involvement' => $request->input('nature_of_involvement'),
        ]);
        ExtensionTag::where('user_id', auth()->id())->where('extension_program_id', $id)->update(['status' => '1']);

        $receiver_user_id = Extensionist::where("extension_program_id", $id)->pluck('user_id')->first();
        $receiver = User::find($receiver_user_id);
        $sender = User::find(auth()->id());
        $url = route('extension-programs.show', $id);

        $notificationData = [
            'receiver' => $receiver->first_name,
            'title' => '',
            'sender' => $sender->first_name.' '.$sender->middle_name.' '.$sender->last_name.' '.$sender->suffix,
            'url' => $url,
            'date' => date('F j, Y, g:i a'),
            'type' => 'ext-confirm'
        ];

        Notification::send($receiver, new ExtensionTagNotification($notificationData));

        if($request->has('notif_id'))
            $sender->notifications()
                        ->where('id', $request->input('notif_id')) // and/or ->where('type', $notificationType)
                        ->get()
                        ->first()
                        ->delete();


        LogActivity::addToLog('Extension program/project/activity was added.');

        return redirect()->route('extension-programs.index')->with('success', 'Extension program/project/activity has been added.');
    }
}
