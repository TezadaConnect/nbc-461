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
};
use App\Models\{
    Research,
    Researcher,
    ResearchDocument,
    ResearchUtilization,
    FormBuilder\DropdownOption,
    FormBuilder\ResearchForm,
    Maintenance\Quarter,
    Maintenance\Department,
    Maintenance\College,
};
use App\Services\CommonService;

class UtilizationController extends Controller
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
    public function index(Research $research)
    {
        $this->authorize('viewAny', ResearchUtilization::class);;

        $currentQuarterYear = Quarter::find(1);

        $utilizationRecords = ResearchUtilization::where('research_id', $research->id)->orderBy('updated_at', 'desc')->get();
        $submissionStatus = array();
        $submitRole = array();
        foreach ($utilizationRecords as $utilization) {
            $submissionStatus[6][$utilization->id] = $this->commonService->getSubmissionStatus($utilization->id, 6)['submissionStatus'];
            $submitRole[$utilization->id] = $this->commonService->getSubmissionStatus($utilization->id, 6)['submitRole'];
        }

        return view('research.utilization.index', compact('research', 'utilizationRecords',
            'currentQuarterYear', 'submissionStatus', 'submitRole'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Research $research)
    {
        $this->authorize('create', ResearchUtilization::class);
        $currentQuarter = Quarter::find(1)->current_quarter;

        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 6)->pluck('is_active')->first() == 0)
            return view('inactive');

        $researchFields = DB::select("CALL get_research_fields_by_form_id('6')");
        $research = collect($research);
        $research = $research->except(['description']);
        return view('research.utilization.create', compact('researchFields', 'research', 'currentQuarter'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Research $research)
    {
        $this->authorize('create', ResearchUtilization::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 6)->pluck('is_active')->first() == 0)
            return view('inactive');

        $currentQuarterYear = Quarter::find(1);

        $request->merge(['research_id' => $research->id,]);
        $input = $request->except(['_token', '_method', 'document']);
        $utilization = ResearchUtilization::create($input);

        $string = str_replace(' ', '-', $request->input('description')); // Replaces all spaces with hyphens.
        $description =  preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        LogActivity::addToLog('Had added a research utilization for "'.$research->title.'".');

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), "RCR-", 'research.utilization.index');
                if(is_string($fileName)) {
                    ResearchDocument::create([
                        'research_id' => $research->id,
                        'research_form_id' => 6,
                        'research_utilization_id' => $utilization->id,
                        'filename' => $fileName,
                    ]);
                } else return $fileName;
            }
        }

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(0, null, $request);

        if($imageChecker) return redirect()->route('research.index')->with('warning', 'Need to attach supporting documents to enable submission');

        return redirect()->route('research.index')->with('success', 'Research utilization has been added.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Research $research, ResearchUtilization $utilization)
    {
        $this->authorize('view', ResearchUtilization::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 6)->pluck('is_active')->first() == 0)
            return view('inactive');

        $researchFields = DB::select("CALL get_research_fields_by_form_id('6')");

        $researchDocuments = ResearchDocument::where('research_utilization_id', $utilization->id)->get()->toArray();

        $research= $research->join('dropdown_options', 'dropdown_options.id', 'research.status')
                ->select('research.*', 'dropdown_options.name as status_name')->first();

        $values = ResearchUtilization::find($utilization->id);

        $values = array_merge($research->toArray(), $values->toArray());

        foreach($researchFields as $field){
            if($field->field_type_name == "dropdown"){
                $dropdownOptions = DropdownOption::where('id', $values[$field->name])->where('is_active', 1)->pluck('name')->first();
                if($dropdownOptions == null)
                    $dropdownOptions = "-";
                $values[$field->name] = $dropdownOptions;
            }
            elseif($field->field_type_name == "college"){
                if($values[$field->name] == '0'){
                    $values[$field->name] = 'N/A';
                }
                else{
                    $college = College::where('id', $values[$field->name])->pluck('name')->first();
                    $values[$field->name] = $college;
                }
            }
            elseif($field->field_type_name == "department"){
                if($values[$field->name] == '0'){
                    $values[$field->name] = 'N/A';
                }
                else{
                    $department = Department::where('id', $values[$field->name])->pluck('name')->first();
                    $values[$field->name] = $department;
                }
            }
        }

        return view('research.utilization.show', compact('research', 'researchFields', 'values', 'researchDocuments'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Research $research, ResearchUtilization $utilization)
    {
        $currentQuarter = Quarter::find(1)->current_quarter;
        $this->authorize('update', ResearchUtilization::class);
        if (Researcher::where('research_id', $research->id)->first()->is_registrant == 0)
            abort(403);
        if(LockController::isLocked($utilization->id, 6)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 6)->pluck('is_active')->first() == 0)
            return view('inactive');

        $researchFields = DB::select("CALL get_research_fields_by_form_id('6')");

        $researchDocuments = ResearchDocument::where('research_utilization_id', $utilization->id)->get()->toArray();

        $research= $research->join('dropdown_options', 'dropdown_options.id', 'research.status')
                ->select('research.*', 'dropdown_options.name as status_name')->first();

        $values = ResearchUtilization::find($utilization->id);
        $values = array_merge($research->toArray(), $values->toArray());

        return view('research.utilization.edit', compact('research', 'researchFields', 'values', 'researchDocuments', 'currentQuarter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Research $research, ResearchUtilization $utilization)
    {
        $currentQuarterYear = Quarter::find(1);
        $this->authorize('update', ResearchUtilization::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 6)->pluck('is_active')->first() == 0)
            return view('inactive');

        $input = $request->except(['_token', '_method', 'document']);
        $utilization->update(['description' => '-clear']);
        $utilization->update($input);
        $string = str_replace(' ', '-', $utilization->description); // Replaces all spaces with hyphens.
        $description =  preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        LogActivity::addToLog('Had updated a research utilization of "'.$research->title.'".');

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), "RU-", 'research.utilization.index');
                if(is_string($fileName)) {
                    ResearchDocument::create([
                        'research_id' => $research->id,
                        'research_form_id' => 6,
                        'research_utilization_id' => $utilization->id,
                        'filename' => $fileName,
                    ]);
                } else return $fileName;
            }
        }

        $imageRecord = ResearchDocument::where('research_utilization_id', $utilization->id)->get();

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(1, $imageRecord, $request);

        if($imageChecker) return redirect()->route('research.index')->with('warning', 'Need to attach supporting documents to enable submission');

        return redirect()->route('research.index')->with('success', 'Research utilization has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Research $research, ResearchUtilization $utilization)
    {
        $this->authorize('delete', ResearchUtilization::class);
        if(LockController::isLocked($utilization->id, 6)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 6)->pluck('is_active')->first() == 0)
            return view('inactive');

        $utilization->delete();

        LogActivity::addToLog('Had deleted a research utilization of "'.$research->title.'".');

        return redirect()->route('research.index', $research->id)->with('success', 'Research utilization has been deleted.');
    }

    /**
     * Display a listing of the resource and enable actions for the resource.
     *
     * @param Int $researchID
     * @param String $actionKeyword which has two values: for-updates and for-submission; to be used in appearance of action buttons
     * @return \Illuminate\Http\Response
     */
    public function showAll($researchId, $actionKeyword)
    {
        $this->authorize('viewAny', ResearchUtilization::class);;

        $currentQuarterYear = Quarter::find(1);
        $research = Research::find($researchId);

        $utilizationRecords = ResearchUtilization::where('research_id', $research->id)->orderBy('updated_at', 'desc')->get();
        $submissionStatus = array();
        $submitRole = array();
        foreach ($utilizationRecords as $utilization) {
            $submissionStatus[6][$utilization->id] = $this->commonService->getSubmissionStatus($utilization->id, 6)['submissionStatus'];
            $submitRole[$utilization->id] = $this->commonService->getSubmissionStatus($utilization->id, 6)['submitRole'];
        }

        return view('research.utilization.show-all', compact('research', 'utilizationRecords',
            'currentQuarterYear', 'submissionStatus', 'submitRole', 'actionKeyword'));
    }
}
