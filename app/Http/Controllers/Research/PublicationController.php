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
    ResearchPresentation,
    ResearchPublication,
    FormBuilder\ResearchForm,
    FormBuilder\DropdownOption,
    Maintenance\Quarter,
};
use App\Services\CommonService;
use App\Services\DateContentService;

class PublicationController extends Controller
{
    protected $storageFileController;
    private $commonService;
    protected $researchController;

    public function __construct(StorageFileController $storageFileController, CommonService $commonService, ResearchController $researchController){
        $this->storageFileController = $storageFileController;
        $this->commonService = $commonService;
        $this->researchController = $researchController;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Research $research)
    {
        $this->authorize('viewAny', ResearchPublication::class);

        $publicationFields = DB::select("CALL get_research_fields_by_form_id('3')");

        $publicationDocuments = ResearchDocument::where('research_id', $research->id)->where('research_form_id', 3)->get()->toArray();
        $publicationRecord = ResearchPublication::where('research_id', $research->id)->first();

        if($publicationRecord == null){
            if ($research->status >= 28)
                return redirect()->route('research.publication.create', $research->id);
            else {
                $value = null;
                return view('research.publication.index', compact('research', 'value'));
            }
        }

        $publicationValues = array_merge(collect($publicationRecord)->toArray(), collect($research)->except(['description'])->toArray());

        $submissionStatus[3][$publicationValues['id']] = $this->commonService->getSubmissionStatus($publicationValues['id'], 3)['submissionStatus'];
        $submitRole[$publicationValues['id']] = $this->commonService->getSubmissionStatus($publicationValues['id'], 3)['submitRole'];

        $value = $this->commonService->getDropdownValues($publicationFields, $publicationValues);
        // $noRequisiteRecords[1] = $this->researchController->getNoRequisites($research)['presentationRecord'];
        // $noRequisiteRecords[2] = $this->researchController->getNoRequisites($research)['publicationRecord'];
        // $noRequisiteRecords[3] = $this->researchController->getNoRequisites($research)['copyrightRecord'];

        return view('research.publication.index', compact('research', 'publicationFields',
            'value', 'publicationDocuments', 'submissionStatus', 'submitRole'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Research $research)
    {
        $this->authorize('create', ResearchPublication::class);
        $currentQuarter = Quarter::find(1)->current_quarter;

        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');

        $researchFields = DB::select("CALL get_research_fields_by_form_id('3')");

        $dropdown_options = [];
        foreach($researchFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        $value = $research;
        $value->toArray();
        $value = collect($research);
        $value = $value->except(['description', 'status']);
        $value = $value->toArray();

        $presentationChecker = ResearchPresentation::where('research_id', $research->id)->first();

        if($presentationChecker == null){
            $researchStatus = DropdownOption::where('dropdown_options.dropdown_id', 7)->where('id', 30)->first();
        }
        else{
            $researchStatus = DropdownOption::where('dropdown_options.dropdown_id', 7)->where('id', 31)->first();
        }

        return view('research.publication.create', compact('researchFields', 'research', 'researchStatus', 'value', 'dropdown_options', 'currentQuarter'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Research $research)
    {
        $this->authorize('create', ResearchPublication::class);
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');

        $publish_date = (new DateContentService())->checkDateContent($request, "publish_date");
        $currentQuarterYear = Quarter::find(1);
        $request->merge(['publish_date' => $publish_date, 'research_id' => $research->id,]);

        $input = $request->except(['_token', '_method', 'status', 'document']);
        $presentationChecker = ResearchPresentation::where('research_id', $research->id)->first();
        if($presentationChecker == null)
            $researchStatus = 30;
        else
            $researchStatus = 31;
        
        $research->update([ 'status' => $researchStatus]);
        $publication = ResearchPublication::create($input);
        LogActivity::addToLog('Had marked the research "'.$research->title.'" as presented.');
        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), "RPUB-", 'research.publication.index');
                if(is_string($fileName)) {
                    ResearchDocument::create([
                        'research_id' => $research->id,
                        'research_form_id' => 3,
                        'filename' => $fileName,
                    ]);
                } else return $fileName;
            }
        }

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(0, null, $request);

        if($imageChecker) return redirect()->route('research.index')->with('warning', 'Need to attach supporting documents to enable submission');

        return redirect()->route('research.index')->with('success', 'Research publication has been added.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Research $research, ResearchPublication $publication)
    {
        $currentQuarter = Quarter::find(1)->current_quarter;
        $this->authorize('update', ResearchPublication::class);

        if (Researcher::where('research_id', $research->id)->first()->is_registrant == 0)
            abort(403);

        if(LockController::isLocked($publication->id, 3)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }
        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');

        $researchFields = DB::select("CALL get_research_fields_by_form_id('3')");

        $dropdown_options = [];
        foreach($researchFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        // $research = array_merge($research->toArray(), $publication->toArray());
        $researchDocuments = ResearchDocument::where('research_id', $research['id'])->where('research_form_id', 3)->get()->toArray();

        $value = $research->toArray();
        $value = collect($research);
        $value = $value->except(['description', 'status']);
        $value = $value->toArray();
        $value = array_merge($value, $publication->toArray());

        $presentationChecker = ResearchPresentation::where('research_id', $research->id)->first();

        if($presentationChecker == null){
            $researchStatus = DropdownOption::where('dropdown_options.dropdown_id', 7)->where('id', 30)->first();
        }
        else{
            $researchStatus = DropdownOption::where('dropdown_options.dropdown_id', 7)->where('id', 31)->first();
        }

        // $noRequisiteRecords[1] = $this->researchController->getNoRequisites($research)['presentationRecord'];
        // $noRequisiteRecords[2] = $this->researchController->getNoRequisites($research)['publicationRecord'];
        // $noRequisiteRecords[3] = $this->researchController->getNoRequisites($research)['copyrightRecord'];

        return view('research.publication.edit', compact('research', 'researchFields', 'researchDocuments', 'value', 'researchStatus', 'dropdown_options', 'currentQuarter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Research $research, ResearchPublication $publication)
    {
        $currentQuarterYear = Quarter::find(1);
        $this->authorize('update', ResearchPublication::class);

        if(ResearchForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        if(ResearchForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');

        $publish_date = (new DateContentService())->checkDateContent($request, "publish_date");
        $request->merge(['publish_date' => $publish_date,]);

        $input = $request->except(['_token', '_method', 'status', 'document']);

        $publication->update(['description' => '-clear']);

        $publication->update($input);

        LogActivity::addToLog('Had updated the publication details of research "'.$research->title.'".');

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), "RPUB-", 'research.publication.index');
                if(is_string($fileName)) {
                    ResearchDocument::create([
                        'research_id' => $research->id,
                        'research_form_id' => 3,
                        'filename' => $fileName,
                    ]);
                } else return $fileName;
            }
        }

        $imageRecord = ResearchDocument::where('research_id', $research->id)->get();

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(1, $imageRecord, $request);

        if($imageChecker) return redirect()->route('research.index')->with('warning', 'Need to attach supporting documents to enable submission');

        return redirect()->route('research.index')->with('success', 'Research publication has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
