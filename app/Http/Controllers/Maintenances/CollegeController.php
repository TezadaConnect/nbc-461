<?php

namespace App\Http\Controllers\Maintenances;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\{
    Employee,
    Maintenance\College,
    Maintenance\Department,
};

class CollegeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', College::class);

        $colleges = College::join('sectors', 'sectors.id', 'colleges.sector_id')->select('colleges.*', 'sectors.name as sector_name')->get();
        return view('maintenances.colleges.index', compact('colleges'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', College::class);

        // return view('maintenances.colleges.create');
        Artisan::call('db:seed', ['--class' => 'CollegeSeeder']);

        return redirect()->route('colleges.index')->with('edit_college_success', 'Office/College/Branch/Campus data synced successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', College::class);

        //
        $validatecollege = $request->validate([
            'name' => 'required|max:200',
        ]);

        $college = College::create([
            'name' => $request->input('name')
        ]);

        return redirect()->route('colleges.create')->with('add_college_success', 'Added college has been saved.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view', College::class);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(College $college)
    {
        //
        $this->authorize('update', College::class);

        $departments = Department::select('name')->where('college_id', $college->id)->get();
        // dd($departments);

        return view('maintenances.colleges.edit', compact('college', 'departments'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, College $college)
    {
        $this->authorize('update', College::class);

        //
        $request->validate([
            'name' => 'required|max:200',
        ]);

        $college->update([
            'name' => $request->input('name')
        ]);

        return redirect()->route('colleges.index')->with('edit_college_success', 'Edit in college has been saved.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(College $college)
    {
        $this->authorize('delete', College::class);

        //
        $college->delete();

        return redirect()->route('colleges.index')->with('edit_college_success', 'College has been deleted.');
    }

    public function getCollegeName($id){

        return College::where('id', $id)->pluck('name')->first();
    }

    public function getCollegeNameUsingDept($deptID){
        $college_id  = Department::where('id', $deptID)->pluck('college_id')->first();
        return College::where('id', $college_id)->pluck('name')->first();
    }

    public function getCollegeByUserTypeAndID($userType, $userID){
        if ($userType == "academic")
            return Employee::where('user_id', $userID)->where('type', 'F')->join('colleges', 'colleges.id', 'employees.college_id')->select('colleges.*')->get() ?? "No college has been tagged by the employee.";
        return Employee::where('user_id', $userID)->where('type', 'A')->join('colleges', 'colleges.id', 'employees.college_id')->select('colleges.*')->get() ?? "No office has been tagged by the employee.";
    }
}
