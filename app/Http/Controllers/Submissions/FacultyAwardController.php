<?php

namespace App\Http\Controllers\Submissions;

use App\Models\Level;
use App\Models\Document;
use App\Models\Department;
use App\Models\Submission;
use App\Models\FacultyAward;
use Illuminate\Http\Request;
use App\Models\TemporaryFile;
use App\Models\FacultyAchievement;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FacultyAwardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $facultyawards = FacultyAward::all();
        $levels = Level::all();

        return view('professors.submissions.facultyaward.create', [
            'departments' => $departments,
            'facultyawards' => $facultyawards,
            'levels' => $levels
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'department' => 'required', 
            'awardreceived' => 'required',
            'awardclass' => 'required',
            'awardbody' => 'required',
            'level' => 'required',
            'venue' => 'required',
            'date_started' => 'required',
            'date_ended' => 'required',
            'document' => 'required',
            'documentdescription' => 'required'
        ]);

        $formId = DB::table('faculty_achievements')->insertGetId([
            'department_id' => $request->input('department'), 
            'award_received' => $request->input('awardreceived'),
            'faculty_award_id' => $request->input('awardclass'),
            'award_body' => $request->input('awardbody'),
            'level' => $request->input('level'),
            'venue' => $request->input('venue'),
            'date_started' => $request->input('date_started'),
            'date_ended' => $request->input('date_ended'),
            'document_description' => $request->input('documentdescription')
        ]);

        if($request->has('document')){
            
            $documents = $request->input('document');
            foreach($documents as $document){
                $temporaryFile = TemporaryFile::where('folder', $document)->first();
                if($temporaryFile){
                    $temporaryPath = "documents/tmp/".$document."/".$temporaryFile->filename;
                    $newPath = "documents/".$temporaryFile->filename;
                    $fileName = $temporaryFile->filename;
                    Storage::move($temporaryPath, $newPath);
                    Storage::deleteDirectory("documents/tmp/".$document);
                    $temporaryFile->delete();

                    Document::create([
                        'filename' => $fileName,
                        'submission_id' => $formId,
                    ]);
                }
            }
        }

        Submission::create([
            'user_id' => Auth::id(),
            'form_id' => $formId,
            'form_name' => 'facultyaward',
            'status' => 1
        ]);

        return redirect()->route('professor.submissions.index')->with('success_submission', 'Submission added successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(FacultyAchievement $facultyaward)
    {
        $department = Department::find($facultyaward->department_id);
        $awardclass = FacultyAward::find($facultyaward->faculty_award_id);
        $level = Level::find($facultyaward->level);
        $documents = Document::where('submission_id' ,$facultyaward->id)->where('deleted_at', NULL)->get();

        return view('professors.submissions.facultyaward.show', [
            'facultyaward' => $facultyaward,
            'department' => $department,
            'awardclass' => $awardclass,
            'level' => $level,
            'documents' => $documents,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(FacultyAchievement $facultyaward)
    {
        $departments = Department::orderBy('name')->get();
        $awardclasses = FacultyAward::all();
        $levels = Level::all();
        $documents = Document::where('submission_id' ,$facultyaward->id)->where('deleted_at', NULL)->get();
        
        return view('professors.submissions.facultyaward.edit', [
            'facultyaward' => $facultyaward,
            'departments' => $departments,
            'awardclasses' => $awardclasses,
            'levels' => $levels,
            'documents' => $documents,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FacultyAchievement $facultyaward)
    {
        $request->validate([
            'department' => 'required', 
            'awardreceived' => 'required',
            'awardclass' => 'required',
            'awardbody' => 'required',
            'level' => 'required',
            'venue' => 'required',
            'date_started' => 'required',
            'date_ended' => 'required',
            'document' => 'required',
            'documentdescription' => 'required'
        ]);

        //Update the row
        $facultyaward->update([
            'department_id' => $request->input('department'), 
            'award_received' => $request->input('awardreceived'),
            'faculty_award_id' => $request->input('awardclass'),
            'award_body' => $request->input('awardbody'),
            'level' => $request->input('level'),
            'venue' => $request->input('venue'),
            'date_started' => $request->input('date_started'),
            'date_ended' => $request->input('date_ended'),
            'document_description' => $request->input('documentdescription')
        ]);

        if($request->has('document')){
            
            $documents = $request->input('document');
            foreach($documents as $document){
                $temporaryFile = TemporaryFile::where('folder', $document)->first();
                if($temporaryFile){
                    $temporaryPath = "documents/tmp/".$document."/".$temporaryFile->filename;
                    $newPath = "documents/".$temporaryFile->filename;
                    $fileName = $temporaryFile->filename;
                    Storage::move($temporaryPath, $newPath);
                    Storage::deleteDirectory("documents/tmp/".$document);
                    $temporaryFile->delete();

                    Document::create([
                        'filename' => $fileName,
                        'submission_id' => $facultyaward->id,
                    ]);
                }
            }
        }

        return redirect()->route('professor.submissions.facultyaward.show', $facultyaward->id)->with('success', 'Form updated successfully.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FacultyAchievement $facultyaward)
    {
        Submission::where('form_id', $facultyaward->id)->delete();
        $facultyaward->delete();
        return redirect()->route('professor.submissions.index')->with('success_submission', 'Submission deleted successfully.');
    }

    public function removeFileInEdit(FacultyAchievement $facultyaward, Request $request){
        Document::where('filename', $request->input('filename'))->delete();
        Storage::delete('documents/'.$request->input('filename'));
        return redirect()->route('professor.submissions.facultyaward.edit', $facultyaward)->with('success', 'Document deleted successfully.');
    }
}
