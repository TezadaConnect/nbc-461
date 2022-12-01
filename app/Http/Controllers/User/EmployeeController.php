<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HRISRegistration\RegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Models\DepartmentEmployee;
use App\Models\Authentication\UserRole;
use App\Models\Maintenance\{
    College,
    Department,
};

class EmployeeController extends Controller
{
    public function __construct(){
        $this->db_ext = DB::connection('mysql_external');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        session(['url' => route('home') ]);
        $user = User::where('id', auth()->id())->first();
        $cbco = College::select('name', 'id')->get();

        // dd($existingCol);
        $currentPos = $this->db_ext->select(" EXEC GetEmployeeCurrentPositionByEmpCode N'$user->emp_code' ");
        if(empty($currentPos)){
            return false;
        }

        $role = "Faculty";

        //if admin
        if($currentPos[0]->EmployeeTypeID == '1')
        $role = "Admin";

        if ($role == "Admin") {
            $existingCol = Employee::where('user_id', auth()->id())->where('type', 'A')->pluck('college_id')->all();
            //For with designation - existingCol2
            $existingCol2 = Employee::where('user_id', auth()->id())->where('type', 'F')->pluck('college_id')->all();
        }
        else {
            $existingCol = Employee::where('user_id', auth()->id())->where('type', 'F')->pluck('college_id')->all();
            $existingCol2 = Employee::where('user_id', auth()->id())->where('type', 'A')->pluck('college_id')->all();
        }

        return view('offices.create', compact('cbco', 'role', 'currentPos', 'existingCol', 'existingCol2'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Employee::where('user_id', auth()->id())->delete();
        foreach($request->input('cbco') as $cbco) {
            if (Employee::where('user_id', auth()->id())->where('type', $request->input('role_type'))->where('college_id', $cbco)->doesntExist()) {
                Employee::create([
                    'user_id' => auth()->id(),
                    'type' => $request->input('role_type'),
                    'college_id' => $cbco,
                ]);
                $officeName = College::where('id', $cbco)->first();
                \LogActivity::addToLog('Had added '.$officeName['name'].' as office to report with.');
            }
        }
        session(['user_type' => Role::where('id', $request->input('role'))->first()->name]);
        if ($request->has('yes')) {
            UserRole::where('user_id', auth()->id())->whereIn('role_id', [1,3])->delete();
            UserRole::create([
                'user_id' => auth()->id(),
                'role_id' => $request->input('role')
            ]);
            if ($request->input('role') == 3) {
                UserRole::create([
                    'user_id' => auth()->id(),
                    'role_id' => 1,
                ]);
            } else {
                UserRole::create([
                    'user_id' => auth()->id(),
                    'role_id' => 3,
                ]);
            }
        }
        
        if ($request->has('designee_cbco')){
            foreach($request->input('designee_cbco') as $cbco) {
                if (Employee::where('user_id', auth()->id())->where('type', $request->input('designee_type'))->where('college_id', $cbco)->doesntExist()) {
                    Employee::create([
                        'user_id' => auth()->id(),
                        'type' => $request->input('designee_type'),
                        'college_id' => $cbco,
                    ]);
                    $officeName = College::where('id', $cbco)->first();
                    \LogActivity::addToLog('Had added '.$officeName['name'].' as office to report with as a designee.');
                }
            }
        }
        if (DepartmentEmployee::where('user_id', auth()->id())->doesntExist())
            return redirect()->route('offices.addDepartment')->with('has_no_department', "Add departments/sections where you commit QAR.");
        if (session('url')){
            // how to logout using redirect
            $checkSched = (new RegistrationController)->scheduleCheck(auth()->id());
            if(!$checkSched) {
                Auth::logout();
                return redirect()->route('home')->with('error', 'Your role and designation has been updated. The college you are in is not scheduled to login today, Try again later.');
            } 
            return redirect(session('url'));
        } else {
            return redirect()->route('account')->with('success', 'Your role and designation has been updated.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $office)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $office)
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($designee_type)
    {
        Employee::where('user_id', auth()->id())->where('type', $designee_type)->delete();
        if ($designee_type == 'A') {
            UserRole::where('user_id', auth()->id())->where('role_id', 3)->delete();
            \LogActivity::addToLog('Had removed designation as Admin.');
        }
        else {
            UserRole::where('user_id', auth()->id())->where('role_id', 1)->delete();
            \LogActivity::addToLog('Had removed designation as Faculty.');
        }
        return redirect()->route('account')->with('success', 'Designation has been removed in your account.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addDepartment(){
        $departments = array();
        $collegeIDs = Employee::where('user_id', auth()->id())->pluck('college_id')->all();
        $departments = Department::whereIn('college_id', $collegeIDs)->select('departments.id', 'departments.name')->get()->toArray();
        $departmentRecordIDs = DepartmentEmployee::where('user_id', auth()->id())->pluck('department_id')->all();
        return view('offices.add-department', compact('departments', 'departmentRecordIDs'));
    }

    /**
     *  Stores the department where the user is committing the accomplishments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeDepartment(Request $request){
        foreach($request->input('department') as $departmentID){
            if (DepartmentEmployee::where('user_id', auth()->id())->where('department_id', $departmentID)->doesntExist()) {
                DepartmentEmployee::create([
                    'user_id' => auth()->id(),
                    'department_id' => $departmentID
                ]);
            }
        }
        return redirect()->route('account')->with('success', 'Your role and designation has been updated.');
    }
}
