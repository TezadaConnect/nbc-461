<?php

namespace App\Http\Controllers\ExtensionPrograms\ExpertServices;

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
    ExpertServiceAcademic,
    ExpertServiceAcademicDocument,
    TemporaryFile,
    FormBuilder\DropdownOption,
    FormBuilder\ExtensionProgramField,
    FormBuilder\ExtensionProgramForm,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
};
use App\Services\CommonService;
use Exception;

class AcademicController extends Controller
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
        $this->authorize('viewAny', ExpertServiceAcademic::class);

        $currentQuarterYear = Quarter::find(1);

        $expertServicesAcademic = ExpertServiceAcademic::where('user_id', auth()->id())
                                        ->join('dropdown_options', 'dropdown_options.id', 'expert_service_academics.classification')
                                        ->join('colleges', 'colleges.id', 'expert_service_academics.college_id')
                                        ->select(DB::raw('expert_service_academics.*, dropdown_options.name as classification, colleges.name as college_name'))
                                        ->orderBy('expert_service_academics.updated_at', 'desc')
                                        ->get();
                                        
        $submissionStatus = array();
        $submitRole = array();
        $reportdata = new ReportDataController;
        foreach ($expertServicesAcademic as $academic) {
            if (LockController::isLocked($academic->id, 11)) {
                $submissionStatus[11][$academic->id] = 1;
                $submitRole[$academic->id] = ReportDataController::getSubmitRole($academic->id, 11);
            }
            else
                $submissionStatus[11][$academic->id] = 0;
            if (empty($reportdata->getDocuments(11, $academic->id)))
                $submissionStatus[11][$academic->id] = 2;
        }

        return view('extension-programs.expert-services.academic.index', compact('expertServicesAcademic',
             'currentQuarterYear', 'submissionStatus', 'submitRole'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', ExpertServiceAcademic::class);
        $currentQuarter = Quarter::find(1)->current_quarter;

        if(ExtensionProgramForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');
        $expertServiceAcademicFields = DB::select("CALL get_extension_program_fields_by_form_id(3)");

        $dropdown_options = [];
        foreach($expertServiceAcademicFields as $field){
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

        return view('extension-programs.expert-services.academic.create', compact('expertServiceAcademicFields', 'colleges', 'departments', 'dropdown_options', 'currentQuarter'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', ExpertServiceAcademic::class);
        if(ExtensionProgramForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');

        $from = date("Y-m-d", strtotime($request->input('from')));
        $to = date("Y-m-d", strtotime($request->input('to')));
        $currentQuarterYear = Quarter::find(1);

        $request->merge([
            'from' => $from,
            'to' => $to,
            'report_quarter' => $currentQuarterYear->current_quarter,
            'report_year' => $currentQuarterYear->current_year,
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
        ]);

        $input = $request->except(['_token', '_method', 'document']);

        $esAcademic = ExpertServiceAcademic::create($input);
        $esAcademic->update(['user_id' => auth()->id()]);

        $string = str_replace(' ', '-', $request->input('description')); // Replaces all spaces with hyphens.
        $description =  preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        $classification = DB::select("CALL get_dropdown_name_by_id($esAcademic->classification)");
        LogActivity::addToLog('Had added an expert service rendered in academic '.strtolower($classification[0]->name).'.');

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), 'ESA-', 'expert-service-in-academic.index');
                if(is_string($fileName)) ExpertServiceAcademicDocument::create(['expert_service_academic_id' => $esAcademic->id, 'filename' => $fileName]);
                else return $fileName;
            }
        }

        if (!$request->has('document')) return redirect()->route('expert-service-in-academic.index')->with('warning', 'Need to attach supporting documents to enable submission');

        return redirect()->route('expert-service-in-academic.index')->with('save_success', 'Expert service rendered in academic '.strtolower($classification[0]->name).' has been added.');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ExpertServiceAcademic $expert_service_in_academic)
    {
        $this->authorize('view', ExpertServiceAcademic::class);

        if (auth()->id() !== $expert_service_in_academic->user_id)
            abort(403);

        if(ExtensionProgramForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');

        $expertServiceAcademicFields = DB::select("CALL get_extension_program_fields_by_form_id(3)");

        $documents = ExpertServiceAcademicDocument::where('expert_service_academic_id', $expert_service_in_academic->id)->get()->toArray();

        $values = $expert_service_in_academic->toArray();

        foreach($expertServiceAcademicFields as $field){
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

        return view('extension-programs.expert-services.academic.show', compact('expertServiceAcademicFields', 'expert_service_in_academic', 'documents', 'values'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpertServiceAcademic $expert_service_in_academic)
    {
        $currentQuarter = Quarter::find(1)->current_quarter;
        $this->authorize('update', ExpertServiceAcademic::class);

        if (auth()->id() !== $expert_service_in_academic->user_id)
            abort(403);

        if(LockController::isLocked($expert_service_in_academic->id, 11)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }

        if(ExtensionProgramForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');
        $expertServiceAcademicFields = DB::select("CALL get_extension_program_fields_by_form_id(3)");

        $dropdown_options = [];
        foreach($expertServiceAcademicFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        $expertServiceAcademicDocuments = ExpertServiceAcademicDocument::where('expert_service_academic_id', $expert_service_in_academic->id)->get()->toArray();

        if(session()->get('user_type') == 'Faculty Employee')
            $colleges = Employee::where('user_id', auth()->id())->where('type', 'F')->pluck('college_id')->all();
        else
            $colleges = Employee::where('user_id', auth()->id())->where('type', 'A')->pluck('college_id')->all();

        $departments = Department::whereIn('college_id', $colleges)->get();

        if ($expert_service_in_academic->department_id != null) {
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(".$expert_service_in_academic->department_id.")");
        }
        else {
            $collegeOfDepartment = DB::select("CALL get_college_and_department_by_department_id(0)");
        }

        $value = $expert_service_in_academic;
        $value->toArray();
        $value = collect($expert_service_in_academic);
        $value = $value->toArray();

        return view('extension-programs.expert-services.academic.edit', compact('value', 'expertServiceAcademicFields', 'expertServiceAcademicDocuments',
            'colleges', 'collegeOfDepartment', 'departments', 'dropdown_options', 'currentQuarter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExpertServiceAcademic $expert_service_in_academic)
    {
        $this->authorize('update', ExpertServiceAcademic::class);
        $currentQuarterYear = Quarter::find(1);

        if(ExtensionProgramForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');

        $from = date("Y-m-d", strtotime($request->input('from')));
        $to = date("Y-m-d", strtotime($request->input('to')));

        $request->merge([
            'from' => $from,
            'to' => $to,
            'college_id' => Department::where('id', $request->input('department_id'))->pluck('college_id')->first(),
            'report_quarter' => $currentQuarterYear->current_quarter,
            'report_year' => $currentQuarterYear->current_year,
        ]);

        $input = $request->except(['_token', '_method', 'document']);
        $expert_service_in_academic->update(['description' => '-clear']);
        $expert_service_in_academic->update($input);

        $classification = DB::select("CALL get_dropdown_name_by_id($expert_service_in_academic->classification)");
        LogActivity::addToLog('Had updated the expert service rendered in academic '.strtolower($classification[0]->name).'.');

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), 'ESA-', 'expert-service-in-academic.index');
                if(is_string($fileName)) ExpertServiceAcademicDocument::create(['expert_service_academic_id' => $expert_service_in_academic->id, 'filename' => $fileName]);
                else return $fileName;
            }
        }

        $imageRecord = ExpertServiceAcademicDocument::where('expert_service_academic_id', $expert_service_in_academic->id)->get();

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(1, $imageRecord, $request);
        if($imageChecker){
            return redirect()->route('expert-service-in-academic.index')->with('warning', 'Need to attach supporting documents to enable submission');
        }
        
        return redirect()->route('expert-service-in-academic.index')->with('save_success', 'Expert service rendered in academic '.strtolower($classification[0]->name).' has been updated.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpertServiceAcademic $expert_service_in_academic)
    {
        $this->authorize('delete', ExpertServiceAcademic::class);

        if(LockController::isLocked($expert_service_in_academic->id, 11)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }

        if(ExtensionProgramForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');
        $expert_service_in_academic->delete();
        $classification = DB::select("CALL get_dropdown_name_by_id($expert_service_in_academic->classification)");
        ExpertServiceAcademicDocument::where('expert_service_academic_id', $expert_service_in_academic->id)->delete();

        LogActivity::addToLog('Had deleted the expert service rendered in academic '.strtolower($classification[0]->name).'.');

        return redirect()->route('expert-service-in-academic.index')->with('success', 'Expert service rendered in academic '.strtolower($classification[0]->name).' has been deleted.');
    }

    public function removeDoc($filename){
        $this->authorize('delete', ExpertServiceAcademic::class);

        if(ExtensionProgramForm::where('id', 3)->pluck('is_active')->first() == 0)
            return view('inactive');
        ExpertServiceAcademicDocument::where('filename', $filename)->delete();
        // Storage::delete('documents/'.$filename);

        LogActivity::addToLog('Had deleted a document of an expert service rendered in academic.');

        return true;
    }
}
