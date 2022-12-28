<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Maintenance\Sector;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RefreshController extends Controller
{
    public function index(){
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        \Artisan::call('optimize:clear');
        // This is a comment
        // Thisi is another comment
        return redirect()->route('home');
    }

    public function migrate(){
        \Artisan::call('migrate');
        \Artisan::call('db:seed');


        return redirect()->route('home');
    }

    //This method is for the adjustment of report approvals of reports that were committed directly to the college/branch/campus/office
    public function reportsAlignment() {
        $reports = Report::all();

        foreach ($reports as $row) {
            if ($row->format == 'a') {
                if ($row->department_id == $row->college_id) {
                    Report::find($row->id)->update([
                        'chairperson_approval' => 1,
                        'updated_at' => DB::raw('updated_at')
                    ]);
                } else {
                    Report::find($row->id)->update([
                        'updated_at' => DB::raw('updated_at')
                    ]);
                }
            } elseif ($row->format == 'f') {
                if ($row->department_id == $row->college_id) {
                    if ($row->department_id >= 227 && $row->department_id <= 248) { // If branch
                        Report::find($row->id)->update([
                            'updated_at' => DB::raw('updated_at')
                        ]);
                    } else {
                        if (($row->report_category_id >= 1 && $row->report_category_id <= 8) || ($row->report_category_id >= 12 && $row->report_category_id <= 14) || ($row->report_category_id >= 34 && $row->report_category_id <= 37) || $row->report_category_id == 22 || $row->report_category_id == 23) {
                            Report::find($row->id)->update([
                                'updated_at' => DB::raw('updated_at')
                            ]);
                        } else {
                            Report::find($row->id)->update([
                                'chairperson_approval' => 1,
                                'updated_at' => DB::raw('updated_at')
                            ]);
                        }
                    }
                } else {
                    Report::find($row->id)->update([
                        'updated_at' => DB::raw('updated_at')
                    ]);
                }
            }
        }

        return redirect()->route('home');
    }

    //This method is for the adjustment of report approvals of reports that were committed directly to the sector.
    //If committed to sector, it must go directly to sector head for approval.
    public function reportsDirectToSector(){
        $reports = Report::all();
        $sectorIDs = Sector::pluck('id')->all();
        foreach ($reports as $row) {
            if ($row->format == 'a') {
                if ($row->department_id == $row->college_id) {
                    if (in_array($row->college_id, $sectorIDs)){
                        Report::find($row->id)->update([
                            'chairperson_approval' => 1,
                            'dean_approval' => 1,
                            'updated_at' => DB::raw('updated_at')
                        ]);
                    }
                }
            }
        }

        return redirect()->route('home');
    }

    public function removeDuplicateInEmployeesTable(){
        $users = User::all();
        // $trashedEmployees = Employee::onlyTrashed()->get();
        // foreach($trashedEmployees as $row){
        //     $row->restore();
        // }
        foreach($users as $user){
            $duplicateFacultyRecords = Employee::select(DB::raw('count(college_id) as occurence, college_id'))
            ->where('user_id', $user->id)
            ->where('type', 'F')
            ->groupBy('college_id')
            ->get();

            $duplicateAdminRecords = Employee::select(DB::raw('count(college_id) as occurence, college_id'))
            ->where('user_id', $user->id)
            ->where('type', 'A')
            ->groupBy('college_id')
            ->get();

            foreach($duplicateFacultyRecords as $row){
                $countFacultyRecordsToDelete = ($row->occurence)-1;
                Employee::where('user_id', $user->id)->where('type', 'F')->where('college_id', $row->college_id)->orderBy('created_at', 'desc')->take($countFacultyRecordsToDelete)->delete();
            }
            foreach($duplicateAdminRecords as $row){
                $countAdminRecordsToDelete = ($row->occurence)-1;
                Employee::where('user_id', $user->id)->where('type', 'A')->where('college_id', $row->college_id)->orderBy('created_at', 'desc')->take($countAdminRecordsToDelete)->delete();
            }
        }
        return redirect()->route('home')->with('success', 'Duplicates in employees table have been removed successfully.');
    }

    public function removeLatestDuplicateInReportsTable(){
        for($quarter = 1; $quarter <= 4; $quarter++){
            $reports = Report::select(DB::raw('count(*) as occurence, report_category_id, report_reference_id, user_id'))
            ->where('report_quarter', $quarter)
            ->groupBy('report_category_id', 'report_reference_id', 'user_id')
            ->get();
            dd($reports);
            foreach($reports as $report){
                $countRecordsToDelete = ($report->occurence)-1;
                Report::where('report_quarter', $report->quarter)
                ->where('report_category_id', $report->report_category_id)
                ->where('report_reference_id', $report->report_reference_id)
                ->where('user_id', $report->user_id)
                ->take($countRecordsToDelete)->delete();

            }
        }
        return redirect()->route('home')->with('success', 'Duplicates in reports table have been removed successfully.');
    }
}

