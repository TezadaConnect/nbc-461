<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
        Report::whereNotIn('report_category_id', [1,2,3,4,5,6,7,12,24,28,33])->where('report_quarter', 3)->chunk(200, function ($reports) {
            foreach($reports as $row){
                Report::where('report_category_id', $row->report_category_id)
                ->where('report_reference_id', $row->report_reference_id)
                ->where('report_quarter', 4)
                ->delete();
            }
        });

        // Report::whereIn('report_category_id', [1,2,3,4,7])->where('report_quarter', 3)->chunk(200, function ($research) {
        //     foreach($research as $row){
        //         $details = json_decode($row->report_details);
        //         // dd($details->status);
        //         Report::where('report_category_id', $row->report_category_id)
        //         ->where('report_reference_id', $row->report_reference_id)
        //         ->where('report_quarter', 4)
        //         ->where('report_code', $row->report_code)
        //         ->where('report_details->status', '!=', $details->status)
        //         ->delete();
        //     }
        // });

        // Report::where('report_category_id', 12)->where('report_quarter', 3)->chunk(200, function ($extension) {
        //     foreach($extension as $row){
        //         $details = json_decode($row->report_details);
        //         Report::where('report_category_id', $row->report_category_id)
        //         ->where('report_reference_id', $row->report_reference_id)
        //         ->where('report_quarter', 4)
        //         ->where('user_id', $row->user_id)
        //         ->where('report_details->status', '!=', $details->status)
        //         ->delete();
        //     }
        // });
        return redirect()->route('home')->with('success', 'Duplicates in reports table have been removed successfully.');
    }

    public function removeUntimelyReportsInTable(){
        Report::whereIn('report_quarter', [3,4])->where('report_year', 2022)->chunk(200, function ($reports) {
            foreach($reports as $row){
                $details = json_decode($row->report_details);
                if($row->report_category_id == 1){
                    if($details->status != "New Commitment"){
                        $date = Carbon::createFromFormat("F d, Y", $details->start_date)->format('Y-m-d');
                        $date = Carbon::parse($date);
                        if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                            Report::find($row->id)->delete();
                        }
                    }
                }
                //  elseif($row->report_category_id == 2){
                //     $date = Carbon::createFromFormat("F d, Y", $details->completion_date)->format('Y-m-d');
                //     $date = Carbon::parse($date);
                //     if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                //         Report::find($row->id)->delete();
                //     }
                // } 
                elseif($row->report_category_id == 3){
                    $date = Carbon::createFromFormat("F d, Y", $details->publish_date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 4){
                    $date = Carbon::createFromFormat("F d, Y", $details->date_presented)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 8){
                    $date = Carbon::createFromFormat("F d, Y", $details->start_date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 9){
                    $date = Carbon::createFromFormat("F d, Y", $details->from)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 10){
                    $date = Carbon::createFromFormat("F d, Y", $details->from)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 12){
                    if($details->from != '-'){
                    $date = Carbon::createFromFormat("F d, Y", $details->from)->format('Y-m-d');
                    $date = Carbon::parse($date);
                        if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                            Report::find($row->id)->delete();
                        }
                    }
                } elseif($row->report_category_id == 13){
                    $date = Carbon::createFromFormat("F d, Y", $details->start_date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 14){
                    $date = Carbon::createFromFormat("F d, Y", $details->start_date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 15){
                    $date = Carbon::createFromFormat("F d, Y", $details->date_started)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 16){
                    $date = Carbon::createFromFormat("F d, Y", $details->date_finished)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 18){
                    $date = Carbon::createFromFormat("F d, Y", $details->date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 19){
                    $date = Carbon::createFromFormat("F d, Y", $details->start_date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 20){
                    $date = Carbon::createFromFormat("F d, Y", $details->start_date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 21){
                    $date = Carbon::createFromFormat("F d, Y", $details->date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 22){
                    $date = Carbon::createFromFormat("F d, Y", $details->date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 29){
                    $date = Carbon::createFromFormat("F d, Y", $details->from)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id >= 30 && $row->report_category_id <= 32){
                    $date = Carbon::createFromFormat("F d, Y", $details->actual_date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 33){
                    $date = Carbon::createFromFormat("F d, Y", $details->end_date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 35 || ($row->report_category_id >= 37 && $row->report_category_id <= 39)){
                    $date = Carbon::createFromFormat("F d, Y", $details->from)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                } elseif($row->report_category_id == 34 || $row->report_category_id == 36){
                    $date = Carbon::createFromFormat("F d, Y", $details->start_date)->format('Y-m-d');
                    $date = Carbon::parse($date);
                    if($date->quarter != $row->report_quarter && substr($date,0,4) != $row->report_year){
                        Report::find($row->id)->delete();
                    }
                }
            }
        });
        return redirect()->route('home')->with('success', 'Duplicates in reports table have been removed successfully.');
    }
}

