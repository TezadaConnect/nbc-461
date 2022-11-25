<?php

namespace App\Http\Controllers\Research;

use App\Helpers\LogActivity;
use App\Http\Controllers\{
    Controller,
    Maintenances\LockController,
    StorageFileController,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    DB,
    Notification,
    Session,
    Validator,
};
use App\Models\{
    Employee,
    Report,
    Research,
    Researcher,
    ResearchCitation,
    ResearchComplete,
    ResearchCopyright,
    ResearchDocument,
    ResearchInvite,
    ResearchTag,
    ResearchPresentation,
    ResearchPublication,
    ResearchUtilization,
    User,
    FormBuilder\DropdownOption,
    FormBuilder\ResearchForm,
    Maintenance\Department,
    Maintenance\Quarter,
};
use App\Notifications\ResearchInviteNotification;
use App\Notifications\ResearchTagNotification;
use App\Rules\Keyword;
use App\Services\CommonService;
use App\Services\DateContentService;

class ResearchController extends Controller
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
        $this->authorize('viewAny', Research::class);
        $year = 'started';
        $statusResearch = "started";//for filter

        $currentQuarterYear = Quarter::find(1);

        $researches = Research::join('researchers', 'researchers.research_id', 'research.id')
                                ->where('researchers.user_id', auth()->id())
                                ->whereNull('researchers.deleted_at')
                                ->join('dropdown_options', 'dropdown_options.id', 'research.status')
                                ->join('colleges', 'colleges.id', 'researchers.college_id')
                                ->select('research.*', 'dropdown_options.name as status_name', 'colleges.name as college_name', 'researchers.is_registrant')
                                ->orderBy('research.updated_at', 'DESC')
                                ->get();
        $submissionStatus = array();                
        $submitRole = array();                
        $isSubmitted = array();  // An array with key-value pairs that contains the return value of a service method that checks if the record is submitted; default value is false                 
        $researchRecords = array();   // An array with key-value pairs that contains the first record of every research record.                         
        foreach ($researches as $row){
            $isSubmitted[1][$row->id] = false; // FORM IDS: 1- regi; 2 - completion; 3 - publication; 4 - presentation; 7 - copyright;    
            $isSubmitted[2][$row->id] = false;            
            $isSubmitted[3][$row->id] = false;            
            $isSubmitted[4][$row->id] = false;            
            $isSubmitted[7][$row->id] = false;             
            $researchRecords['regi'][$row->id] = Research::where('id', $row->id)->first();
            // Research Registration
            $isSubmitted[1][$row->id] = LockController::isReportSubmitted($row->id, 1);
            $submissionStatus[1][$row->id] = $this->commonService->getSubmissionStatus($row->id, 1)['submissionStatus'];
            $submitRole[1][$row->id] = $this->commonService->getSubmissionStatus($row->id, 1)['submitRole'];
            // Research Completion
            $researchRecords['completion'][$row->id] = ResearchComplete::where('research_id', $row->id)->first();
            if ($researchRecords['completion'][$row->id] != null) {
                $isSubmitted[2][$row->id] = LockController::isReportSubmitted($row->id, 2);
                $submissionStatus[2][$row->id] = $this->commonService->getSubmissionStatus($row->id, 2)['submissionStatus'];
                $submitRole[2][$row->id] = $this->commonService->getSubmissionStatus($row->id, 2)['submitRole'];
            }
            // Research Publication
            $researchRecords['publication'][$row->id] = ResearchPublication::where('research_id', $row->id)->first();
            if ($researchRecords['publication'][$row->id] != null) {
                $isSubmitted[3][$row->id] = LockController::isReportSubmitted($row->id, 3);
                $submissionStatus[3][$row->id] = $this->commonService->getSubmissionStatus($row->id, 3)['submissionStatus'];
                $submitRole[3][$row->id] = $this->commonService->getSubmissionStatus($row->id, 3)['submitRole'];
            }
            // Research Presentation
            $researchRecords['presentation'][$row->id] = ResearchPresentation::where('research_id', $row->id)->first();
            if ($researchRecords['presentation'][$row->id] != null) {
                $isSubmitted[4][$row->id] = LockController::isReportSubmitted($row->id, 4);
                $submissionStatus[4][$row->id] = $this->commonService->getSubmissionStatus($row->id, 4)['submissionStatus'];
                $submitRole[4][$row->id] = $this->commonService->getSubmissionStatus($row->id, 4)['submitRole'];
            }
            // Research Copyright
            $researchRecords['copyright'][$row->id] = ResearchCopyright::where('research_id', $row->id)->first();
            if ($researchRecords['copyright'][$row->id] != null) {
                $isSubmitted[7][$row->id] = LockController::isReportSubmitted($row->id, 7);
                $submissionStatus[7][$row->id] = $this->commonService->getSubmissionStatus($row->id, 7)['submissionStatus'];
                $submitRole[7][$row->id] = $this->commonService->getSubmissionStatus($row->id, 7)['submitRole'];
            }
            // Research Citations
            $researchRecords['citation'][$row->id] = ResearchCitation::where('research_id', $row->id)->first();
            // Research Utiizations
            $researchRecords['utilization'][$row->id] = ResearchUtilization::where('research_id', $row->id)->first();
        }
        $invites = ResearchTag::join('research', 'research.id', 'research_tags.research_id')
                                ->join('users', 'users.id', 'research_tags.sender_id')
                                ->where('research_tags.user_id', auth()->id())
                                ->select('users.first_name', 'users.last_name', 'users.middle_name', 'users.suffix',
                                    'research.title', 'research_tags.research_id',
                                    'research_tags.status')
                                ->where('research_tags.status', null)
                                ->get();

        return view('research.index', compact('researches', 'year', 'statusResearch', 'invites',
             'currentQuarterYear', 'submissionStatus', 'submitRole', 'researchRecords', 'isSubmitted'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Research::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
        return view('inactive');
        $currentQuarter = Quarter::find(1)->current_quarter;

        $researchFields = DB::select("CALL get_research_fields_by_form_id(1)");

        $dropdown_options = [];
        foreach($researchFields as $field){
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

        return view('research.create', compact('researchFields', 'colleges', 'departments', 'dropdown_options', 'allUsers', 'currentQuarter'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Research::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
        return view('inactive');

        $value = $request->input('funding_amount');
        $value = (float) str_replace(",", "", $value);
        $value = number_format($value,2,'.','');

        $start_date = (new DateContentService())->checkDateContent($request, "start_date");
        $target_date = (new DateContentService())->checkDateContent($request, "target_date");
        $currentQuarterYear = Quarter::find(1);

        $request->merge([
            'start_date' => $start_date,
            'target_date' => $target_date,
            'funding_amount' => $value,
            'untagged_researchers' => $request->input('researchers'),
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
        ]);

        $validator =  Validator::make($request->all(), [
            // 'keywords' => new Keyword,
            'title' => 'unique:research',
            'college_id' => 'required',
            'department_id' => 'required',
        ]);

        if ($validator->fails())
            return redirect()->back()->with('error', 'The title has already been taken.');

        // $discipline = DropdownOption::where('id', $request->discipline)->pluck('name')->first();
        $input = $request->except(['_token', 'document', 'funding_amount', 'tagged_collaborators', 'nature_of_involvement', 'college_id', 'department_id']);

        $funding_amount = $request->funding_amount;
        $funding_amount = str_replace( ',' , '', $funding_amount);
        $research = Research::create([ 'funding_amount' => $funding_amount,]);
        Researcher::create([
            'research_id' => $research->id,
            'department_id' => $request->input('department_id'),
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
            'user_id' => auth()->id(),
            'nature_of_involvement' => $request->input('nature_of_involvement'),
            'is_registrant' => 1,
        ]);

        $research->update($input);

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), "RR-", 'research.index');
                if(is_string($fileName)) {
                    ResearchDocument::create([
                        'research_id' => $research->id,
                        'research_form_id' => 1,
                        'filename' => $fileName,
                    ]);
                } else return $fileName;
            }
        }

        $this->commonService->addTaggedUsers($request->input('tagged_collaborators'), $research->id, 'research');
        
        LogActivity::addToLog('Had added a research entitled "'.$request->input('title').'".');

        return redirect()->route('research.index')->with('success', 'Research has been registered.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Research $research)
    {
        $this->authorize('view', Research::class);

        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
        return view('inactive');

        $research= Research::where('research.id', $research->id)->join('researchers', 'researchers.research_id', 'research.id')
                ->join('dropdown_options', 'dropdown_options.id', 'research.status')
                ->select('research.*', 'dropdown_options.name as status_name', 'researchers.*')->first();

        $researchFields = DB::select("CALL get_research_fields_by_form_id('1')");
        $researchValues = $research->toArray();
        $researchDocuments = ResearchDocument::where('research_id', $research->id)->where('research_form_id', 1)->get()->toArray();

        $submissionStatus[1][$research->id] = $this->commonService->getSubmissionStatus($research->id, 1)['submissionStatus'];
        $submitRole[$research->id] = $this->commonService->getSubmissionStatus($research->id, 1)['submitRole'];
        
        $value = $this->commonService->getDropdownValues($researchFields, $researchValues);

        if ($research->department_id != null) {
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(".$research->department_id.")");
        }
        else {
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(0)");
        }

        $colleges = Employee::where('user_id', auth()->id())->join('colleges', 'colleges.id', 'employees.college_id')->select('colleges.*')->get();
        return view('research.show', compact('research', 'researchFields', 'value', 'researchDocuments',
             'colleges', 'collegeOfDepartment', 'submissionStatus', 'submitRole'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Research $research)
    {
        $currentQuarter = Quarter::find(1)->current_quarter;
        $this->authorize('update', Research::class);

        if (Researcher::where('research_id', $research->id)->first()->is_registrant == 0)
            abort(403);

        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
        return view('inactive');

        $researchFields = DB::select("CALL get_research_fields_by_form_id(1)");

        $dropdown_options = [];
        foreach($researchFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;
            }
        }

        
        // if($firstResearch['id'] == $research->id){
        $researcher = Researcher::where('research_id', $research->id)->where('user_id', auth()->id())->first();
        $values = $research->toArray();
        $values['nature_of_involvement'] = $researcher->nature_of_involvement;
        $values['department_id'] = $researcher->department_id;

        $researchDocuments = ResearchDocument::where('research_id', $research->id)->where('research_form_id', 1)->get()->toArray();
        if(session()->get('user_type') == 'Faculty Employee')
            $colleges = Employee::where('user_id', auth()->id())->where('type', 'F')->pluck('college_id')->all();
        else
            $colleges = Employee::where('user_id', auth()->id())->where('type', 'A')->pluck('college_id')->all();

        $departments = Department::whereIn('college_id', $colleges)->get();

        if ($researcher->department_id != null)
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(".$researcher->department_id.")");
        else
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(0)");
        
        $allUsers = $this->commonService->getAllUserNames();
        $taggedUserIDs = ResearchTag::where('research_id', $research->id)->pluck('user_id')->all();
        $researchStatus = DropdownOption::where('dropdown_options.dropdown_id', 7)->where('id', $research->status)->first();
        if(Researcher::where('research_id', $research->id)->where('user_id', auth()->id())->first()['is_registrant'] == 1){
            $values['researchers'] = $research->untagged_researchers;
            return view('research.edit', compact('research', 'researchFields', 'values', 'researchDocuments', 'colleges', 'researchStatus', 'collegeOfDepartment', 'departments', 'dropdown_options', 'currentQuarter', 'allUsers', 'taggedUserIDs'));
        }

        return view('research.edit-non-lead', compact('research', 'researchFields', 'values', 'researchDocuments', 'colleges', 'researchStatus', 'collegeOfDepartment', 'departments', 'dropdown_options', 'currentQuarter', 'allUsers', 'taggedUserIDs'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Research $research)
    {
        if ($research->status != 27){
            if(LockController::isLocked($research->id, 1))
                return redirect()->back()->with('cannot_access', 'Accomplishment was already submitted!');
        }
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');

        $value = $request->input('funding_amount');
        $value = (float) str_replace(",", "", $value);
        $value = number_format($value,2,'.','');
        $data = $request->except(['_token', '_method', 'document', 'funding_amount', 'tagged_collaborators', 'nature_of_involvement', 'college_id', 'department_id']);
        foreach ($data as $key => $value) {
            if ((new DateContentService())->isValidDate($value) == true)
                $request->merge([ $key => (new DateContentService())->checkDateContent($request, $key) ]);
        }
        $request->merge([
            'funding_amount' => $value,
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
        ]);
        $request->validate([
            // 'keywords' => new Keyword,
            'college_id' => 'required',
            'department_id' => 'required',
        ]);
        $input = $request->except(['_token', '_method', 'document', 'funding_amount', 'tagged_collaborators', 'nature_of_involvement', 'college_id', 'department_id']);
        $funding_amount = $request->funding_amount;
        $funding_amount = str_replace( ',' , '', $funding_amount);

        $research->update(['description' => '-clear']);
        $research->update($input);
        $research->update(['funding_amount' => $funding_amount,]);

        Researcher::where('research_id', $research->id)->where('user_id', auth()->id())
            ->update([
                'college_id' => $request->input('college_id'),
                'department_id' => $request->input('department_id'),
                'nature_of_involvement' => $request->input('nature_of_involvement'),
            ]);
        $taggedUsersID = ResearchTag::where('research_id', $research->id)->pluck('user_id')->all();
        if ($request->input('tagged_collaborators') == null){
            Researcher::where('research_id', $research->id)->where('user_id', '!=', auth()->id())->delete();
            ResearchTag::where('research_id', $research->id)->where('user_id', '!=', auth()->id())->delete();
            $research->update([
                'researchers' => $request->input('researchers'),
                'untagged_researchers' => $request->input('researchers'),
            ]);
        }
        elseif (array_diff($taggedUsersID, $request->input('tagged_collaborators')) != null){
            foreach($request->input('tagged_collaborators') as $tagID){
                if (!in_array($tagID, $taggedUsersID)){
                    ResearchTag::create(['research_id' => $research->id, 'user_id' => $tagID, 'sender_id' => auth()->id(), ]);
                }
            }
            foreach($taggedUsersID as $notifiedUser){
                if (!in_array($notifiedUser, $request->input('tagged_collaborators'))){
                    ResearchTag::where('research_id', $research->id)->where('user_id', $notifiedUser)->delete();
                    Researcher::where('research_id', $research->id)->where('user_id', $notifiedUser)->delete();
                }
            }
        }

        $researcherExploded = explode("/", $request->input('researchers'));
        foreach(ResearchTag::where('research_id', $research->id)->pluck('user_id')->all() as $finalResearcherID){
            $user = User::find($finalResearcherID);
            if ($user->middle_name != '') {
                array_push($researcherExploded, $user->last_name.', '.$user->first_name.' '.substr($user->middle_name,0,1).'.');
            } else {
                array_push($researcherExploded, $user->last_name.', '.$user->first_name);
            }
        }
        $research->update([
            'researchers' => implode("/", $researcherExploded),
            'untagged_researchers' => $request->input('researchers'),
        ]);
        
        $this->commonService->addTaggedUsers($request->input('tagged_collaborators'), $research->id, 'research');

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), "RR-", 'research.index');
                if(is_string($fileName)) {
                    ResearchDocument::create([
                        'research_id' => $research->id,
                        'research_form_id' => 1,
                        'filename' => $fileName,

                    ]);
                } else return $fileName;
            }
        }

        \LogActivity::addToLog('Had updated the details of research "'.$research->title.'".');

        return redirect()->route('research.index')->with('success', 'Research has been updated.');
    }

    public function updateNonLead (Request $request, Research $research)
    {
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        Researcher::where('research_id', $research->id)->where('user_id', auth()->id())
            ->update([
                'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
                'department_id' => $request->input('department_id'),
                'nature_of_involvement' => $request->input('nature_of_involvement'),
            ]);

        LogActivity::addToLog('Had updated the details of research "'.$research->title.'".');

        return redirect()->route('research.index')->with('success', 'Research has been updated.');
    }

    /**
     * Remove the specified resource from storage. UPDATED: Use the destroy function to change the status of research into deferred.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Research $research)
    {
        $this->authorize('delete', Research::class);

        if(LockController::isLocked($research->id, 1))
            return redirect()->back()->with('cannot_access', 'Accomplishment was already submitted!');

        $research->update(['status' => 32]);
        return redirect()->route('research.index')->with('success', 'Research status has been changed to deferred.');
    }

    public function addResearch($research_id, Request $request){
        $currentQuarterYear = Quarter::find(1);
        $currentQuarter = Quarter::find(1)->current_quarter;

        $this->authorize('create', Research::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');

            $research = Research::where('research.id', $research_id)->join('dropdown_options', 'dropdown_options.id', 'research.status')
            ->join('currencies', 'currencies.id', 'research.currency_funding_amount')
            ->select('research.*', 'dropdown_options.name as status_name', 'currencies.code as currency_funding_amount')
            ->first();

        if ($research == null)
            return redirect()->route('research.index')->with('cannot_access', 'The research not found in the system.');

        if (ResearchTag::where('research_id', $research_id)->where('user_id', auth()->id())->doesntExist())
            return redirect()->route('research.index')->with('cannot_access', 'The research not found in the system. The lead researcher may removed you as a co-researcher.');

        $researchFields = DB::select("CALL get_research_fields_by_form_id(1)");

        $dropdown_options = [];
        foreach($researchFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        $research = collect($research);
        $research = $research->except(['nature_of_involvement', 'college_id', 'department_id']);
        $values = $research->toArray();
        $research = json_decode(json_encode($research), FALSE);
        // $research = collect($research);
        $researchers = Research::where('id', $research->id)->pluck('researchers')->all();

        $researchDocuments = ResearchDocument::where('research_id', $research->id)->where('research_form_id', 1)->get()->toArray();

        $colleges = Employee::where('user_id', auth()->id())->pluck('college_id')->all();

        $departments = Department::whereIn('college_id', $colleges)->get();

        $researchStatus = DropdownOption::where('dropdown_options.dropdown_id', 7)->where('id', $research->status)->first();

        $notificationID = $request->get('id');

        return view('research.code-create', compact('research', 'researchers', 'researchDocuments', 'values', 'researchFields', 'colleges', 'researchStatus', 'notificationID', 'departments', 'dropdown_options', 'currentQuarter'));
    }

    public function saveResearch($research_id, Request $request){
        $this->authorize('create', Research::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');

        Researcher::create([
            'research_id' => $research_id,
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
            'department_id' => $request->input('department_id'),
            'user_id' => auth()->id(),
            'nature_of_involvement' => $request->input('nature_of_involvement'),
        ]);

        $receiver = User::find(auth()->id());
        $research_title = Research::where('id', $research_id)->pluck('title')->first();
        $sender = User::find(auth()->id());
        $url = route('research.show', $research_id);

        $notificationData = [
            'receiver' => $receiver->first_name,
            'title' => $research_title,
            'sender' => $sender->first_name.' '.$sender->middle_name.' '.$sender->last_name.' '.$sender->suffix,
            'url' => $url,
            'date' => date('F j, Y, g:i a'),
            'type' => 'res-confirm'
        ];

        Notification::send($receiver, new ResearchTagNotification($notificationData));

        if($request->has('notif_id'))
            $sender->notifications()
                        ->where('id', $request->input('notif_id')) // and/or ->where('type', $notificationType)
                        ->get()
                        ->first()
                        ->delete();

        LogActivity::addToLog('Had saved a research entitled "'.$research_title.'".');


        return redirect()->route('research.index')->with('success', 'Research has been saved.');
    }
}

