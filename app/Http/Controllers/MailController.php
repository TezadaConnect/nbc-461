<?php

namespace App\Http\Controllers;

use App\Models\Authentication\UserRole;
use App\Models\Chairperson;
use App\Models\Dean;
use App\Models\FacultyExtensionist;
use App\Models\FacultyResearcher;
use App\Models\Maintenance\College;
use App\Models\Maintenance\Department;
use App\Models\Maintenance\Quarter;
use App\Models\Maintenance\Sector;
use App\Models\Report;
use App\Models\SectorHead;
use App\Models\User;
use App\Notifications\PendingConsolidatdQarNotification;
use App\Services\CommonService;
use Illuminate\Support\Facades\Notification;

class MailController extends Controller
{

    private $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function sendMailToPendingIPOApprover($pending)
    {
        $currentQuarterYear = Quarter::find(1);
        $quarter = $currentQuarterYear->current_quarter;
        $year = $currentQuarterYear->current_year;

        $ipqmso_accomps = Report::select(
            'reports.*',
            'report_categories.name as report_category',
            'users.last_name',
            'users.first_name',
            'users.middle_name',
            'users.suffix'
        )->join('report_categories', 'reports.report_category_id', 'report_categories.id')->join('users', 'users.id', 'reports.user_id')->where('reports.report_year', $year)->where('reports.report_quarter', $quarter)->get();

        $ipqmso_accomps = $this->commonService->getStatusOfIPO($ipqmso_accomps, $this->approvalHolderArr[$pending] ?? 'researcher_approval');

        $arrayHolder = [];
        foreach ($ipqmso_accomps as $item) {
            if ($pending == 0) array_push($arrayHolder, $item->research_cluster_id);
            if ($pending == 1) array_push($arrayHolder, $item->college_id);
            if ($pending == 2) array_push($arrayHolder, $item->department_id);
            if ($pending == 3) array_push($arrayHolder, $item->college_id);
            if ($pending == 4) array_push($arrayHolder, $item->sector_id);
        }

        $this->getPendingUser($arrayHolder, $pending);

        return redirect()->back()->with('success', 'Email Notification sent!');
    }

    private function getPendingUser($arrayHolder, $pending)
    {

        if ($pending == 0) {
            $researcher = FacultyResearcher::whereIn('cluster_id', $arrayHolder)->get();
            return $this->sendMailNotification($researcher);
        }
        if ($pending == 1) {
            $extensionist = FacultyExtensionist::whereIn('college_id', $arrayHolder)->get();
            return $this->sendMailNotification($extensionist);
        }
        if ($pending == 2) {
            $chair = Chairperson::whereIn('department_id', $arrayHolder)->get();
            return $this->sendMailNotification($chair);
            // $item = User::where("id", $chair->user_id)->get();
        }
        if ($pending == 3) {
            $dean = Dean::whereIn('college_id', $arrayHolder)->get();
            return $this->sendMailNotification($dean);
            // $item = User::where("id", $dean->user_id)->first();
        }
        if ($pending == 4) {
            $sectorHead = SectorHead::whereIn('sector_id', $arrayHolder)->get();
            return $this->sendMailNotification($sectorHead);
        }

        if ($pending == 5) {
            $ipoStaff = UserRole::where('role_id', 8)->with('user')->get();
            foreach ($ipoStaff as $stafRole) {
                $user = User::where("id", $stafRole->user_id)->first();
                Notification::send($user, new PendingConsolidatdQarNotification([
                    "receiver" => $user->first_name
                ]));
            }
            return null;
        }
    }

    private function sendMailNotification($items)
    {
        foreach ($items as $value) {
            $user = User::where("id", $value->user_id)->first();
            $notificationData = [
                "receiver" => $user->first_name
            ];
            Notification::send($user, new PendingConsolidatdQarNotification($notificationData));
        }
    }
}
