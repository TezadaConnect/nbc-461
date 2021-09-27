<?php

namespace App\Http\Controllers\FormBuilder;

use Illuminate\Http\Request;
use App\Models\FormBuilder\Form;
use App\Models\FormBuilder\QarForm;
use App\Http\Controllers\Controller;
use App\Models\FormBuilder\NonQarForm;

class FormController extends Controller
{
    /**
     * lists all the form created
     */
    public function index()
    {
        //get all forms
        $forms = Form::all();

        // get forms that are not assign using the ids from the other 2 tables
        $qarFormsId = QarForm::pluck('form_id')->all();
        $nonQarFormsId = NonQarForm::pluck('form_id')->all();
        $notAssignedForms = Form::whereNotIn('id', $qarFormsId)->whereNotIn('id',$nonQarFormsId)->get();
        
        return view('formbuilder.forms.index', compact('forms', 'notAssignedForms', 'qarFormsId', 'nonQarFormsId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Form::create([
            'name' => $request->input('name'),
            'form_name' => $request->input('form_name'),
            'javascript' => null,
        ]);

        return redirect()->route('admin.forms.index')->with('success', $request->input('name').' added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Form $form)
    {
        return view('formbuilder.forms.show', compact('form'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Form $form)
    {
        $form->update([
            'name' => $request->input('name'),
            'form_name' => $request->input('form_name'),
            'javascript' => $request->input('javascript') ?? null,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Form $form)
    {
        $form->delete();

        return redirect()->route('admin.forms.index')->with('success', 'Form deleted successfully.');
    }
    
    /**
     * saves the arrangement of QAR Forms
     */
    public function qarArrange(Request $request){
        // delete all existing records
        QarForm::truncate();
    
        //insert the new records sent from ajax request
        $data = json_decode($request->data, true);
        for($i = 0; $i < count($data); $i++){
            QarForm::insert([
                'form_id' => (int) $data[$i]['form_id']
            ]);
        }
        return true;
    }

        
    /**
     * saves the arrangement of NON QAR Forms
     */
    public function nonQarArrange(Request $request){
        // delete all existing records
        NonQarForm::truncate();

        //insert the new records sent from ajax request
        $data = json_decode($request->data, true);
        for($i = 0; $i < count($data); $i++){
            NonQarForm::insert([
                'form_id' => (int) $data[$i]['form_id']
            ]);
        }
        return true;
    }
}
