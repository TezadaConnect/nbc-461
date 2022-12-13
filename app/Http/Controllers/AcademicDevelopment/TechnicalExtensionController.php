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
    Chairperson,
    Dean,
    TechnicalExtension,
    TechnicalExtensionDocument,
    TemporaryFile,
    FormBuilder\ExtensionProgramForm,
    FormBuilder\DropdownOption,
    Maintenance\College,
    Maintenance\Quarter,
    Maintenance\Department
};
use App\Services\CommonService;
use Exception;

class TechnicalExtensionController extends Controller
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
        $this->authorize('viewAny', TechnicalExtension::class);

        $currentQuarterYear = Quarter::find(1);

        $technical_extensions = TechnicalExtension::where('user_id', auth()->id())
                                ->select(DB::raw('technical_extensions.*'))
                                ->orderBy('technical_extensions.updated_at', 'desc')->get();

        $submissionStatus = array();
        $reportdata = new ReportDataController;
        foreach ($technical_extensions as $technical_extension) {
            if (LockController::isLocked($technical_extension->id, 23))
                $submissionStatus[23][$technical_extension->id] = 1;
            else
                $submissionStatus[23][$technical_extension->id] = 0;
            if (empty($reportdata->getDocuments(23, $technical_extension->id)))
                $submissionStatus[23][$technical_extension->id] = 2;
        }

        return view('academic-development.technical-extension.index', compact('technical_extensions', 'currentQuarterYear',
            'submissionStatus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', TechnicalExtension::class);
        $currentQuarter = Quarter::find(1)->current_quarter;

        if(ExtensionProgramForm::where('id', 12)->pluck('is_active')->first() == 0)
            return view('inactive');
        $extensionFields = DB::select("CALL get_extension_program_fields_by_form_id(12)");

        $dropdown_options = [];
        foreach($extensionFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        $deans = Dean::where('user_id', auth()->id())->pluck('college_id')->all();
        $chairpersons = Chairperson::where('user_id', auth()->id())->join('departments', 'departments.id', 'chairpeople.department_id')->pluck('departments.college_id')->all();
        $colleges = array_merge($deans, $chairpersons);

        $colleges = College::whereIn('id', array_values($colleges))->select('colleges.*')->get();

        return view('academic-development.technical-extension.create', compact('extensionFields', 'colleges', 'dropdown_options', 'currentQuarter'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', TechnicalExtension::class);

        $value = $request->input('total_profit');
        $value = (float) str_replace(",", "", $value);
        $value = number_format($value,2,'.','');

        $currentQuarterYear = Quarter::find(1);

        $request->merge([
            'total_profit' => $value,
            'report_quarter' => $currentQuarterYear->current_quarter,
            'report_year' => $currentQuarterYear->current_year,
        ]);

        if(ExtensionProgramForm::where('id', 12)->pluck('is_active')->first() == 0)
            return view('inactive');
        $input = $request->except(['_token', '_method', 'document']);

        $technical_extension = TechnicalExtension::create($input);
        $technical_extension->update(['user_id' => auth()->id()]);

        LogActivity::addToLog('Had added a technical extension program, project, or activity.');

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), 'TEPPA-', 'technical-extension.index');
                if(is_string($fileName)) TechnicalExtensionDocument::create(['technical_extension_id' => $technical_extension->id, 'filename' => $fileName]);
                else return $fileName;
            }
        }

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(0, null, $request);
        if($imageChecker) return redirect()->route('technical-extension.index')->with('warning', 'Need to attach supporting documents to enable submission');

        return redirect()->route('technical-extension.index')->with('save_success', 'Technical extension program, project, or activity has been added.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(TechnicalExtension $technical_extension)
    {
        $this->authorize('view', TechnicalExtension::class);

        if (auth()->id() !== $technical_extension->user_id)
            abort(403);

        if(ExtensionProgramForm::where('id', 12)->pluck('is_active')->first() == 0)
            return view('inactive');
        $extensionFields = DB::select("CALL get_extension_program_fields_by_form_id(12)");

        $documents = TechnicalExtensionDocument::where('technical_extension_id', $technical_extension->id)->get()->toArray();

        $values = $technical_extension->toArray();

        foreach($extensionFields as $field){
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

        return view('academic-development.technical-extension.show', compact('extensionFields', 'technical_extension', 'documents', 'values'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(TechnicalExtension $technical_extension)
    {
        $this->authorize('update', TechnicalExtension::class);

        if (auth()->id() !== $technical_extension->user_id)
            abort(403);
        $currentQuarter = Quarter::find(1)->current_quarter;

        if(LockController::isLocked($technical_extension->id, 23)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }

        if(ExtensionProgramForm::where('id', 12)->pluck('is_active')->first() == 0)
            return view('inactive');
        $extensionFields = DB::select("CALL get_extension_program_fields_by_form_id(12)");

        $dropdown_options = [];
        foreach($extensionFields as $field){
            if($field->field_type_name == "dropdown" || $field->field_type_name == "text"){
                $dropdownOptions = DropdownOption::where('dropdown_id', $field->dropdown_id)->where('is_active', 1)->get();
                $dropdown_options[$field->name] = $dropdownOptions;

            }
        }

        $documents = TechnicalExtensionDocument::where('technical_extension_id', $technical_extension->id)->get()->toArray();

        $values = $technical_extension->toArray();

        $deans = Dean::where('user_id', auth()->id())->pluck('college_id')->all();
        $chairpersons = Chairperson::where('user_id', auth()->id())->join('departments', 'departments.id', 'chairpeople.department_id')->pluck('departments.college_id')->all();
        $colleges = array_merge($deans, $chairpersons);

        $colleges = College::whereIn('id', array_values($colleges))
                    ->select('colleges.*')->get();

        return view('academic-development.technical-extension.edit', compact('extensionFields', 'technical_extension', 'documents', 'values', 'colleges', 'dropdown_options', 'currentQuarter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TechnicalExtension $technical_extension)
    {
        $this->authorize('update', TechnicalExtension::class);
        $currentQuarterYear = Quarter::find(1);

        $value = $request->input('total_profit');
        $value = (float) str_replace(",", "", $value);
        $value = number_format($value,2,'.','');

        $request->merge([
            'total_profit' => $value,
            'report_quarter' => $currentQuarterYear->current_quarter,
            'report_year' => $currentQuarterYear->current_year,
        ]);

        if(ExtensionProgramForm::where('id', 12)->pluck('is_active')->first() == 0)
            return view('inactive');
        $input = $request->except(['_token', '_method', 'document']);

        $technical_extension->update(['description' => '-clear']);

        $technical_extension->update($input);

        LogActivity::addToLog('Had updated a technical extension program, project, or activity.');

        if(!empty($request->file(['document']))){      
            foreach($request->file(['document']) as $document){
                $fileName = $this->commonService->fileUploadHandler($document, $request->input("description"), 'TEPPA-', 'technical-extension.index');
                if(is_string($fileName)) TechnicalExtensionDocument::create(['technical_extension_id' => $technical_extension->id, 'filename' => $fileName]);
                else return $fileName;
            }
        }

        $imageRecord = TechnicalExtensionDocument::where('technical_extension_id', $technical_extension->id)->get();

        $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(1, $imageRecord, $request);

        if($imageChecker) return redirect()->route('technical-extension.index')->with('warning', 'Need to attach supporting documents to enable submission');

        return redirect()->route('technical-extension.index')->with('save_success', 'Technical extension program, project, or activity has been updated.');

        // if($request->has('document')){
        //     try {
        //         $documents = $request->input('document');
        //         foreach($documents as $document){
        //             $temporaryFile = TemporaryFile::where('folder', $document)->first();
        //             if($temporaryFile){
        //                 $temporaryPath = "documents/tmp/".$document."/".$temporaryFile->filename;
        //                 $info = pathinfo(storage_path().'/documents/tmp/'.$document."/".$temporaryFile->filename);
        //                 $ext = $info['extension'];
        //                 $fileName = 'TEPPA-'.$this->storageFileController->abbrev($request->input('description')).'-'.now()->timestamp.uniqid().'.'.$ext;
        //                 $newPath = "documents/".$fileName;
        //                 Storage::move($temporaryPath, $newPath);
        //                 Storage::deleteDirectory("documents/tmp/".$document);
        //                 $temporaryFile->delete();
        //                 TechnicalExtensionDocument::create([
        //                     'technical_extension_id' => $technical_extension->id,
        //                     'filename' => $fileName,
        //                 ]);
        //             }
        //         }
        //     } catch (Exception $th) {
        //         return redirect()->back()->with('error', 'Request timeout, Unable to upload, Please try again!' );
        //     }
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TechnicalExtension $technical_extension)
    {
        $this->authorize('delete', TechnicalExtension::class);

        if(LockController::isLocked($technical_extension->id, 23)){
            return redirect()->back()->with('cannot_access', 'Cannot be edited because you already submitted this accomplishment. You can edit it again in the next quarter.');
        }

        if(ExtensionProgramForm::where('id', 12)->pluck('is_active')->first() == 0)
            return view('inactive');
        TechnicalExtensionDocument::where('technical_extension_id', $technical_extension->id)->delete();
        $technical_extension->delete();

        LogActivity::addToLog('Had deleted a technical extension program, project, or activity.');

        return redirect()->route('technical-extension.index')->with('success', 'Technical extension program, project, or activity has been deleted.');
    }

    public function removeDoc($filename){
        $this->authorize('delete', TechnicalExtension::class);

        if(ExtensionProgramForm::where('id', 12)->pluck('is_active')->first() == 0)
            return view('inactive');
        TechnicalExtensionDocument::where('filename', $filename)->delete();

        LogActivity::addToLog('Had deleted a document of a technical extension program, project, or activity.');

        return true;
    }
}
