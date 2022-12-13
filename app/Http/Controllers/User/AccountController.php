<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\{
    User,
    DepartmentEmployee,
    Employee,
};
use App\Models\Authentication\UserRole;

class AccountController extends Controller
{
    public function index()
    {
        $user = User::find(auth()->id());

        $db_ext = DB::connection('mysql_external');

        $employeeDetail = $db_ext->select("EXEC GetEmployeeByEmpCode N'$user->emp_code'");
        $accountDetail = $db_ext->select("EXEC GetUserAccount N'$user->user_account_id'");
        $roles = UserRole::where('user_id', $user->id)->join('roles', 'roles.id', 'user_roles.role_id')
                            ->pluck('roles.name')
                            ->all();
        $roles = implode(', ', $roles);
        $employeeSectorsCbcoDepartment = Employee::where('employees.user_id', $user->id)
                            ->join('colleges', 'employees.college_id', 'colleges.id')
                            ->select('employees.id', 'colleges.name as collegeName')
                            ->get();
        $employeeTypeOfUser = Employee::where('user_id', auth()->id())->groupBy('type')->oldest()->get();
// dd($employeeTypeOfUser);
        $designations = [];
        foreach($employeeTypeOfUser as $employee) {
            $designations[$employee->type] = Employee::where('user_id', auth()->id())
                ->where('employees.type', $employee->type)
                ->join('colleges', 'colleges.id', 'employees.college_id')
                ->select('colleges.name', 'colleges.id')
                ->get();
        }
        $employeeTypeByOrder = Employee::where('user_id', auth()->id())->orderBy('type')->oldest()->get();
        $departmentNames = [];
        foreach($employeeTypeOfUser as $employee) {
            foreach($employeeTypeByOrder as $employeeRecord){
                $departmentNames = DepartmentEmployee::where('user_id', auth()->id())
                    ->join('departments', 'departments.id', 'department_employees.department_id')
                    ->select('departments.name', 'departments.id')
                    ->get();
            }
        }
        // dd($departmentNames);
        return view('account', compact('accountDetail', 'employeeDetail', 'roles', 'employeeSectorsCbcoDepartment', 'user',
            'employeeTypeOfUser', 'designations', 'departmentNames'));
    }
}
