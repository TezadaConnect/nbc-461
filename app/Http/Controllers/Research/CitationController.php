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
    ResearchCitation,
    ResearchDocument,
    FormBuilder\DropdownOption,
    FormBuilder\ResearchForm,
    Maintenance\Quarter,
};
use App\Services\CommonService;

class CitationController extends Controller
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
        $this->authorize('viewAny', ResearchCitation::class);

        $currentQuarterYear = Quarter::find(1);
        $citationRecords = ResearchCitation::where('research_id', $research->id)->orderBy('updated_at', 'desc')->get();
        $submissionStatus = array();
        $submitRole = array();
        foreach ($citationRecords as $citation) {
            $submissionStatus[5][$citation->id] = $this->commonService->getSubmissionStatus($citation->id, 5)['submissionStatus'];
            $submitRole[$citation->id] = $this->commonService->getSubmissionStatus($citation->id, 5)['submitRole'];
        }
        $firstResearch = Research::where('id', $research->id)->first();
        
        return view('research.citation.index', compact('research', 'citationRecords',
            'currentQuarterYear', 'submissionStatus', 'submitRole'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Research $research)
    {
        $this->authorize('create', ResearchCitation::class);
        $currentQuarter = Quarter::find(1)->current_quarter;

        if(ResearchForm::where('id', 5)->pluck('is_active')->first() == 0)
            return view('inactive');

        $researchFields = DB::select("CALL get_research_fields_by_form_id('5')");

        $dropdown_options = [];
        foreach($researchFields as $field){
            if($field->field_type_name == "dropdown"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        $research = collect($research);
        $research = $research->except(['description']);
        return view('research.citation.create', compact('researchFields', 'research', 'dropdown_options', 'currentQuarter'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Research $research)
    {
        $this->authorize('create', ResearchCitation::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 5)->pluck('is_active')->first() == 0)
            return view('inactive');

        $currentQuarterYear = Quarter::find(1);
        $request->merge(['research_id' => $research->id,]);
        $input = $request->except(['_token', '_method', 'document']);
        $citation = ResearchCitation::create($input);

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), "RCT-", 'research.citation.index');
                if(is_string($fileName)) {
                    ResearchDocument::create([
                        'research_id' => $research->id,
                        'research_form_id' => 5,
                        'research_citation_id' => $citation->id,
                        'filename' => $fileName,
                    ]);
                } else return $fileName;
            }
        }

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(0, null, $request);

        if($imageChecker) return redirect()->route('research.index')->with('warning', 'Need to attach supporting documents to enable submission');
        \LogActivity::addToLog('Had added a research citation for "'.$research->title.'".');

        return redirect()->route('research.index')->with('success', 'Research citation has been added.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Research $research, ResearchCitation $citation)
    {
        $this->authorize('view', ResearchCitation::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 5)->pluck('is_active')->first() == 0)
            return view('inactive');

        $citationFields = DB::select("CALL get_research_fields_by_form_id('5')");

        $citationDocuments = ResearchDocument::where('research_citation_id', $citation->id)->get()->toArray();
        $citationRecord = ResearchCitation::find($citation->id);

        $values = array_merge($research->toArray(), $citationRecord->toArray());
        
        $values = $this->commonService->getDropdownValues($citationFields, $values);

        return view('research.citation.show', compact('research', 'citationFields', 'values', 'citationDocuments'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Research $research, ResearchCitation $citation)
    {
        $currentQuarter = Quarter::find(1)->current_quarter;
        if (Researcher::where('research_id', $research->id)->first()->is_registrant == 0)
            abort(403);

        $this->authorize('update', ResearchCitation::class);

        if(LockController::isLocked($citation->id, 5)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }

        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 5)->pluck('is_active')->first() == 0)
            return view('inactive');

        $researchFields = DB::select("CALL get_research_fields_by_form_id('5')");

        $dropdown_options = [];
        foreach($researchFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }
        $researchDocuments = ResearchDocument::where('research_citation_id', $citation->id)->get()->toArray();
        $values = ResearchCitation::find($citation->id);
        $values = array_merge($research->toArray(), $values->toArray());

        return view('research.citation.edit', compact('research', 'researchFields', 'values', 'researchDocuments', 'dropdown_options', 'currentQuarter'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Research $research, ResearchCitation $citation)
    {
        $currentQuarterYear = Quarter::find(1);
        $this->authorize('update', ResearchCitation::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 5)->pluck('is_active')->first() == 0)
            return view('inactive');

        $input = $request->except(['_token', '_method', 'document']);
        $citation->update(['description' => '-clear']);
        $citation->update($input);
        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), "RCT-", 'research.citation.index');
                if(is_string($fileName)) {
                    ResearchDocument::create([
                        'research_id' => $research->id,
                        'research_form_id' => 5,
                        'research_citation_id' => $citation->id,
                        'filename' => $fileName,
                    ]);
                } else return $fileName;
            }
        }

        $imageRecord = ResearchDocument::where('research_citation_id', $citation->id)->get();

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(1, $imageRecord, $request);

        if($imageChecker) return redirect()->route('research.index')->with('warning', 'Need to attach supporting documents to enable submission');
        \LogActivity::addToLog('Had updated a research citation of "'.$research->title.'".');

        return redirect()->route('research.index')->with('success', 'Research Citation Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Research $research, ResearchCitation $citation)
    {
        $this->authorize('delete', ResearchCitation::class);
        if(LockController::isLocked($research->id, 1)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }
        if(LockController::isLocked($citation->id, 5)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 5)->pluck('is_active')->first() == 0)
            return view('inactive');

        $citation->delete();

        LogActivity::addToLog('Had deleted a research citation of "'.$research->title.'".');

        return redirect()->route('research.citation.index', $research->id)->with('success', 'Research citation has been deleted.');
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
        $this->authorize('viewAny', ResearchCitation::class);

        $currentQuarterYear = Quarter::find(1);

        $research = Research::find($researchId);
        $citationRecords = ResearchCitation::where('research_id', $research->id)->orderBy('updated_at', 'desc')->get();
        $research = Research::where('research.id', $research->id)->join('researchers', 'researchers.research_id', 'research.id')
        ->join('dropdown_options', 'dropdown_options.id', 'research.status')
        ->select('researchers.*', 'research.*', 'dropdown_options.name as status_name')->first();
        
        $submissionStatus = array();
        $submitRole = array();
        foreach ($citationRecords as $citation) {
            $submissionStatus[5][$citation->id] = $this->commonService->getSubmissionStatus($citation->id, 5)['submissionStatus'];
            $submitRole[$citation->id] = $this->commonService->getSubmissionStatus($citation->id, 5)['submitRole'];
        }

        return view('research.citation.show-all', compact('research', 'citationRecords',
            'currentQuarterYear', 'submissionStatus', 'submitRole', 'actionKeyword'));
    }
}
