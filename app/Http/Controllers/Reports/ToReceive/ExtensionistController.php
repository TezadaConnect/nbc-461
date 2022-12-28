<?php

namespace App\Http\Controllers\Reports\ToReceive;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\{
    Associate,
    Chairperson,
    Dean,
    DenyReason,
    FacultyExtensionist,
    FacultyResearcher,
    Report,
    SectorHead,
    User,
    Authentication\UserRole,
    Maintenance\College,
    Maintenance\Department,
    Maintenance\Quarter,
    Maintenance\ReportCategory,
};
use App\Notifications\ReceiveNotification;
use App\Notifications\ReturnNotification;
use App\Services\CommonService;
use App\Services\ToReceiveReportAuthorizationService;

class ExtensionistController extends Controller
{
    private $commonService;

    public function __construct(CommonService $commonService){
        $this->commonService = $commonService;
    }
    public function index(){
        $authorize = (new ToReceiveReportAuthorizationService())->authorizeReceiveIndividualExtension();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }
        //role and department/ college id
        $roles = UserRole::where('user_id', auth()->id())->pluck('role_id')->all();

        $assignments = $this->commonService->getAssignmentsByCurrentRoles($roles);
        $reportsToReview = collect();
        $currentQuarterYear = Quarter::find(1);

        foreach ($assignments[11] as $row){
            $tempReports = Report::where('reports.report_year', $currentQuarterYear->current_year)
                // ->where('reports.report_quarter', $currentQuarterYear->current_quarter)
                ->whereIn('reports.report_quarter', [3,4])
                ->where('reports.format', 'f')
                ->whereIn('reports.report_category_id', [12, 13, 14, 22, 23, 37])
                ->where('college_id', $row->college_id)->where('extensionist_approval', null)
                ->select('reports.*', 'colleges.name as college_name', 'report_categories.name as report_category', 'users.last_name', 'users.first_name','users.middle_name', 'users.suffix')
                ->join('colleges', 'reports.college_id', 'colleges.id')
                ->join('report_categories', 'reports.report_category_id', 'report_categories.id')
                ->join('users', 'reports.user_id', 'users.id')
                ->orderBy('reports.created_at', 'DESC')
                ->get();

            $reportsToReview = $reportsToReview->concat($tempReports);
        }

        $college_names = [];
        $department_names = [];
        foreach($reportsToReview as $row){
            $temp_college_name = College::select('name')->where('id', $row->college_id)->first();
            $temp_department_name = Department::select('name')->where('id', $row->department_id)->first();
            $row->report_details = json_decode($row->report_details, false);

            if($temp_college_name == null)
                $college_names[$row->id] = '-';
            else
                $college_names[$row->id] = $temp_college_name;
            if($temp_department_name == null)
                $department_names[$row->id] = '-';
            else
                $department_names[$row->id] = $temp_department_name;
        }

        return view('reports.to-receive.extensionists.index', compact('reportsToReview', 'roles', 'college_names', 'department_names', 'assignments'));
    }

    public function accept($report_id){
        $authorize = (new ToReceiveReportAuthorizationService())->authorizeReceiveIndividualExtension();
        if (!($authorize)) {abort(403, 'Unauthorized action.');}
        $report = Report::find($report_id);
        $senderName = FacultyExtensionist::join('colleges', 'colleges.id', 'faculty_extensionists.college_id')
            ->join('users', 'users.id', 'faculty_extensionists.user_id')
            ->where('faculty_extensionists.college_id', $report->college_id)
            ->select('colleges.name as college_name', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.suffix')
            ->first();
        $report_category_name = ReportCategory::where('id', $report->report_category_id)->pluck('name')->first();
        $url = route('reports.consolidate.myaccomplishments');
        $indivReport = Report::where('report_category_id', $report->report_category_id)->where('report_reference_id', $report->report_reference_id)->get();
        foreach($indivReport as $row){
            $receiverData = User::find($row->user_id);
            Report::where('id', $row->id)->update(['extensionist_approval' => 1, 'chairperson_approval' => 1]);
            $notificationData = [
                'sender' => $senderName->first_name.' '.$senderName->middle_name.' '.$senderName->last_name.' '.$senderName->suffix.' ('.$senderName->college_name.' Extensionist)',
                'receiver' => $receiverData->first_name,
                'url' => $url,
                'category_name' => $report_category_name,
                'user_id' => $receiverData->id,
                'accomplishment_type' => 'individual',
                'date' => date('F j, Y, g:i a'),
                'databaseOnly' => 1
            ];
            Notification::send($receiverData, new ReceiveNotification($notificationData));
        }
        \LogActivity::addToLog('Extensionist received an accomplishment.');

        return redirect()->route('extensionist.index')->with('success', 'Report has been added in college consolidation of reports.');

    }
    public function rejectCreate($report_id){
        $authorize = (new ToReceiveReportAuthorizationService())->authorizeReceiveIndividualExtension();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        return view('reports.to-receive.extensionists.reject', compact('report_id'));
    }

    public function reject($report_id, Request $request){
        $authorize = (new ToReceiveReportAuthorizationService())->authorizeReceiveIndividualExtension();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }
        $report = Report::find($report_id);
        $senderName = FacultyExtensionist::join('colleges', 'colleges.id', 'faculty_extensionists.college_id')
        ->join('users', 'users.id', 'faculty_extensionists.user_id')
        ->where('faculty_extensionists.college_id', $report->college_id)
        ->select('colleges.name as college_name', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.suffix')
        ->first();
        $report_category_name = ReportCategory::where('id', $report->report_category_id)->pluck('name')->first();
        $url = route('reports.consolidate.myaccomplishments');
        
        if ($report->report_category_id == 12){
            $indivReport = Report::where('report_category_id', $report->report_category_id)->where('report_reference_id', $report->report_reference_id)->get();
            foreach($indivReport as $row){
                $returnData = User::find($row->user_id);
                DenyReason::create([
                    'report_id' => $row->id,
                    'user_id' => auth()->id(),
                    'position_name' => 'extensionist',
                    'reason' => $request->input('reason'),
                ]);
                Report::where('id', $row->id)->update([
                    'extensionist_approval' => 0
                ]);
                $notificationData = [
                    'sender' => $senderName->first_name.' '.$senderName->middle_name.' '.$senderName->last_name.' '.$senderName->suffix.' ('.$senderName->college_name.' Extensionist)',
                    'receiver' => $returnData->first_name,
                    'url' => $url,
                    'category_name' => $report_category_name,
                    'user_id' => $returnData->id,
                    'reason' => $request->input('reason'),
                    'accomplishment_type' => 'individual',
                    'date' => date('F j, Y, g:i a'),
                    'databaseOnly' => 0
                ];
                Notification::send($returnData, new ReturnNotification($notificationData));
            }
        } else{
            $returnData = User::find($report->user_id);
            DenyReason::create([
                'report_id' => $report_id,
                'user_id' => auth()->id(),
                'position_name' => 'extensionist',
                'reason' => $request->input('reason'),
            ]);
            Report::where('id', $report_id)->update([
                'extensionist_approval' => 0
            ]);
            $notificationData = [
                'sender' => $senderName->first_name.' '.$senderName->middle_name.' '.$senderName->last_name.' '.$senderName->suffix.' ('.$senderName->college_name.' Extensionist)',
                'receiver' => $returnData->first_name,
                'url' => $url,
                'category_name' => $report_category_name,
                'user_id' => $returnData->id,
                'reason' => $request->input('reason'),
                'accomplishment_type' => 'individual',
                'date' => date('F j, Y, g:i a'),
                'databaseOnly' => 0
            ];
            Notification::send($returnData, new ReturnNotification($notificationData));
        }
        \LogActivity::addToLog('Extensionist returned an accomplishment.');

        return redirect()->route('extensionist.index')->with('success', 'Report has been returned to the owner.');
    }

    public function acceptSelected(Request $request){
        $authorize = (new ToReceiveReportAuthorizationService())->authorizeReceiveIndividualExtension();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        $reportIds = $request->input('report_id');

        $count = 0;
        foreach($reportIds as $report_id){
            Report::where('id', $report_id)->update(['extensionist_approval' => 1, 'chairperson_approval' => 1]);

            $report = Report::find($report_id);

            $receiverData = User::find($report->user_id);
            $senderName = FacultyExtensionist::join('colleges', 'colleges.id', 'faculty_extensionists.college_id')
                    ->join('users', 'users.id', 'faculty_extensionists.user_id')
                    ->where('faculty_extensionists.college_id', $report->college_id)
                    ->select('colleges.name as college_name', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.suffix')
                    ->first();

            $report_category_name = ReportCategory::where('id', $report->report_category_id)->pluck('name')->first();

            $url = route('reports.consolidate.myaccomplishments');


            $notificationData = [
                'sender' => $senderName->first_name.' '.$senderName->middle_name.' '.$senderName->last_name.' '.$senderName->suffix.' ('.$senderName->college_name.' Extensionist)',
                'receiver' => $receiverData->first_name,
                'url' => $url,
                'category_name' => $report_category_name,
                'user_id' => $receiverData->id,
                'accomplishment_type' => 'individual',
                'date' => date('F j, Y, g:i a'),
                'databaseOnly' => 1
            ];

            Notification::send($receiverData, new ReceiveNotification($notificationData));

            $count++;
        }

        \LogActivity::addToLog('Extensionist received '.$count.' accomplishments.');

        return redirect()->route('extensionist.index')->with('success', 'Report/s added in college consolidation of reports.');
    }

    public function denySelected(Request $request){
        $authorize = (new ToReceiveReportAuthorizationService())->authorizeReceiveIndividualExtension();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        $reportIds = $request->input('report_id');
        return view('reports.to-receive.extensionists.reject-select', compact('reportIds'));
    }

    public function rejectSelected(Request $request){
        $authorize = (new ToReceiveReportAuthorizationService())->authorizeReceiveIndividualExtension();
        if (!($authorize)) {
            abort(403, 'Unauthorized action.');
        }

        $reportIds = $request->input('report_id');

        $count = 0;
        foreach($reportIds as $report_id){
            if($request->input('reason_'.$report_id) == null)
                continue;
            Report::where('id', $report_id)->update(['extensionist_approval' => 0]);
            DenyReason::create([
                'report_id' => $report_id,
                'user_id' => auth()->id(),
                'position_name' => 'extensionist',
                'reason' => $request->input('reason_'.$report_id),
            ]);

            $report = Report::find($report_id);

            $returnData = User::find($report->user_id);
            $senderName = FacultyExtensionist::join('colleges', 'colleges.id', 'faculty_extensionists.college_id')
                ->join('users', 'users.id', 'faculty_extensionists.user_id')
                ->where('faculty_extensionists.college_id', $report->college_id)
                ->select('colleges.name as college_name', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.suffix')
                ->first();

            $report_category_name = ReportCategory::where('id', $report->report_category_id)->pluck('name')->first();

            $url = route('reports.consolidate.myaccomplishments');


            $notificationData = [
                'sender' => $senderName->first_name.' '.$senderName->middle_name.' '.$senderName->last_name.' '.$senderName->suffix.' ('.$senderName->college_name.' Extensionist)',
                'receiver' => $returnData->first_name,
                'url' => $url,
                'category_name' => $report_category_name,
                'user_id' => $returnData->id,
                'reason' => $request->input('reason'),
                'accomplishment_type' => 'individual',
                'date' => date('F j, Y, g:i a'),
                'databaseOnly' => 0
            ];

            Notification::send($returnData, new ReturnNotification($notificationData));

            $count++;
        }
        \LogActivity::addToLog('Extensionist returned '.$count.' accomplishments.');
        return redirect()->route('extensionist.index')->with('success', 'Report/s returned to the owner/s.');
    }
}
