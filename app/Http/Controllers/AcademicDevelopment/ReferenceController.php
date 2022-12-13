<?php

namespace App\Http\Controllers\AcademicDevelopment;

use App\Helpers\LogActivity;
use App\Http\Controllers\{
    Controller,
    Maintenances\LockController,
    Reports\ReportDataController,
    StorageFileController,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\{
    Employee,
    Reference,
    ReferenceDocument,
    TemporaryFile,
    FormBuilder\DropdownOption,
    FormBuilder\AcademicDevelopmentForm,
    Maintenance\College,
    Maintenance\Quarter,
    Maintenance\Department,
};
use App\Services\CommonService;
use App\Services\DateContentService;
use Exception;

class ReferenceController extends Controller
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
        $this->authorize('viewAny', Reference::class);

        $currentQuarterYear = Quarter::find(1);

        $allRtmmi = Reference::where('user_id', auth()->id())
                                    ->join('dropdown_options', 'dropdown_options.id', 'references.category')
                                    ->join('colleges', 'colleges.id', 'references.college_id')
                                    ->select('references.*', 'dropdown_options.name as category_name', 'colleges.name as college_name')
                                    ->orderBy('references.updated_at', 'desc')
                                    ->get();

        $submissionStatus = array();
        $submitRole = array();
        $reportdata = new ReportDataController;
        foreach ($allRtmmi as $rtmmi) {
            if (LockController::isLocked($rtmmi->id, 15)) {
                $submissionStatus[15][$rtmmi->id] = 1;
                $submitRole[$rtmmi->id] = ReportDataController::getSubmitRole($rtmmi->id, 15);
            }
            else
                $submissionStatus[15][$rtmmi->id] = 0;
            if (empty($reportdata->getDocuments(15, $rtmmi->id)))
                $submissionStatus[15][$rtmmi->id] = 2;
        }

        return view('academic-development.references.index', compact('allRtmmi', 'currentQuarterYear', 'submissionStatus', 'submitRole'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Reference::class);
        $currentQuarter = Quarter::find(1)->current_quarter;

        if(AcademicDevelopmentForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        $referenceFields = DB::select("CALL get_academic_development_fields_by_form_id(1)");

        $dropdown_options = [];
        foreach($referenceFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        $colleges = Employee::where('user_id', auth()->id())->where('type', 'F')->pluck('college_id')->all();

        $departments = Department::whereIn('college_id', $colleges)->get();

        return view('academic-development.references.create', compact('referenceFields', 'colleges', 'departments', 'dropdown_options', 'currentQuarter'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Reference::class);

        if(AcademicDevelopmentForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');

        $date_started = (new DateContentService())->checkDateContent($request, "date_started");
        $date_completed = (new DateContentService())->checkDateContent($request, "date_completed");
        $date_published = (new DateContentService())->checkDateContent($request, "date_published");
        $currentQuarterYear = Quarter::find(1);

        $request->merge([
            'date_started' => $date_started,
            'date_completed' => $date_completed,
            'date_published' => $date_published,
            'report_quarter' => $currentQuarterYear->current_quarter,
            'report_year' => $currentQuarterYear->current_year,
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
        ]);

        $input = $request->except(['_token', '_method', 'document']);

        $rtmmi = Reference::create($input);
        $rtmmi->update(['user_id' => auth()->id()]);

        $accomplished = DB::select("CALL get_dropdown_name_by_id(".$rtmmi->category.")");

        $accomplishment = $accomplished[0]->name;

        LogActivity::addToLog('Had added '.$accomplishment.' entitled "'.$request->input('title').'".');

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), 'RTMMI-', 'rtmmi.index');
                if(is_string($fileName)) ReferenceDocument::create(['reference_id' => $rtmmi->id, 'filename' => $fileName]);
                else return $fileName;
            }
        }

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(0, null, $request);
        if($imageChecker){
            return redirect()->route('rtmmi.index')->with('warning', 'Need to attach supporting documents to enable submission');
        }

        return redirect()->route('rtmmi.index')->with(['save_success' => ucfirst($accomplishment[0]), 'action' => 'added.' ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Reference $rtmmi)
    {
        $this->authorize('view', Reference::class);

        if (auth()->id() !== $rtmmi->user_id)
            abort(403);

        if(AcademicDevelopmentForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        $referenceDocuments = ReferenceDocument::where('reference_id', $rtmmi->id)->get()->toArray();

        $category = DB::select("CALL get_dropdown_name_by_id(".$rtmmi->category.")");

        $referenceFields = DB::select("CALL get_academic_development_fields_by_form_id(1)");

        $values = $rtmmi->toArray();

        foreach($referenceFields as $field){
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


        return view('academic-development.references.show', compact('rtmmi', 'referenceDocuments', 'category', 'referenceFields', 'values'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Reference $rtmmi)
    {
        $this->authorize('update', Reference::class);
        $currentQuarter = Quarter::find(1)->current_quarter;

        if (auth()->id() !== $rtmmi->user_id)
            abort(403);

        if(AcademicDevelopmentForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');

        if(LockController::isLocked($rtmmi->id, 15)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }

        $referenceFields = DB::select("CALL get_academic_development_fields_by_form_id(1)");

        $dropdown_options = [];
        foreach($referenceFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        $referenceDocuments = ReferenceDocument::where('reference_id', $rtmmi->id)->get()->toArray();

        $category = DB::select("CALL get_dropdown_name_by_id(".$rtmmi->category.")");

        $colleges = Employee::where('user_id', auth()->id())->where('type', 'F')->pluck('college_id')->all();

        $departments = Department::whereIn('college_id', $colleges)->get();

        if ($rtmmi->department_id != null) {
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(".$rtmmi->department_id.")");
        }
        else {
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(0)");
        }

        $value = $rtmmi;
        $value->toArray();
        $value = collect($rtmmi);
        $value = $value->toArray();

        return view('academic-development.references.edit', compact('value', 'referenceFields', 'referenceDocuments', 'colleges', 'category', 'collegeOfDepartment', 'departments', 'dropdown_options', 'currentQuarter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request,
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reference $rtmmi)
    {
        $this->authorize('update', Reference::class);
        $currentQuarterYear = Quarter::find(1);

        if(AcademicDevelopmentForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');

        $date_started = (new DateContentService())->checkDateContent($request, "date_started");
        $date_completed = (new DateContentService())->checkDateContent($request, "date_completed");
        $date_published = (new DateContentService())->checkDateContent($request, "date_published");

        $request->merge([
            'date_started' => $date_started,
            'date_completed' => $date_completed,
            'date_published' => $date_published,
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
            'report_quarter' => $currentQuarterYear->current_quarter,
            'report_year' => $currentQuarterYear->current_year,
        ]);

        $input = $request->except(['_token', '_method', 'document']);

        $rtmmi->update(['description' => '-clear']);

        $rtmmi->update($input);

        $accomplished = DB::select("CALL get_dropdown_name_by_id(".$rtmmi->category.")");

        $accomplished = collect($accomplished);
        $accomplishment = $accomplished->pluck('name');

        LogActivity::addToLog('Had updated the '.$rtmmi->category.' entitled "'.$rtmmi->title.'".');

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), 'RTMMI-', 'rtmmi.index');
                if(is_string($fileName)) ReferenceDocument::create(['reference_id' => $rtmmi->id, 'filename' => $fileName]);
                else return $fileName;
            }
        }

        $imageRecord = ReferenceDocument::where('reference_id', $rtmmi->id)->get();

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(1, $imageRecord, $request);
        if($imageChecker){
            return redirect()->route('rtmmi.index')->with('warning', 'Need to attach supporting documents to enable submission');
        }
        

        return redirect()->route('rtmmi.index')->with('save_success', ucfirst($accomplishment[0]))->with('action', 'updated.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reference $rtmmi)
    {
        $this->authorize('delete', Reference::class);

        if(AcademicDevelopmentForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');

        if(LockController::isLocked($rtmmi->id, 15)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }

        $rtmmi->delete();
        ReferenceDocument::where('reference_id', $rtmmi->id)->delete();

        $accomplished = DB::select("CALL get_dropdown_name_by_id(".$rtmmi->category.")");

        $accomplished = collect($accomplished);
        $accomplishment = $accomplished->pluck('name');

        LogActivity::addToLog('Had deleted the '.$rtmmi->category.' entitled "'.$rtmmi->title.'".');

        return redirect()->route('rtmmi.index')->with('success', ucfirst($accomplishment[0]))
                            ->with('action', 'deleted.');
    }

    public function removeDoc($filename){

        $this->authorize('delete', Reference::class);

        if(AcademicDevelopmentForm::where('id', 1)->pluck('is_active')->first() == 0)
            return view('inactive');
        ReferenceDocument::where('filename', $filename)->delete();
        // Storage::delete('documents/'.$filename);

        LogActivity::addToLog('Had deleted a document of a reference, textbook, module, monograph, or IM.');

        return true;
    }
}
