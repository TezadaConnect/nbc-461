<?php

namespace App\Http\Controllers;

use App\Mail\CommonMail;
use App\Models\Authentication\UserRole;
use App\Models\Maintenance\College;
use App\Models\Maintenance\Department;
use App\Models\Maintenance\Quarter;
use App\Models\Maintenance\Sector;
use App\Models\Report;
use App\Models\User;
use App\Notifications\PendingConsolidatdQarNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
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
            if ($pending === 0) array_push($arrayHolder, "");
            if ($pending === 1) array_push($arrayHolder, "");
            if ($pending === 2) array_push($arrayHolder, $item->college_id);
            if ($pending === 3) array_push($arrayHolder, $item->department_id);
            if ($pending === 4) array_push($arrayHolder, $item->sector_id);
            if ($pending === 5) array_push($arrayHolder, "");
        }

        $user = $this->getPendingUser($arrayHolder, $pending);
        $notificationData["receiver"] = $user->first_name;
        $user->notify(new PendingConsolidatdQarNotification($notificationData));
    }

    private function getPendingUser($arrayHolder, $pending)
    {
        $item = null;
        $userIdHolder = [];

        if ($pending === 0) {
        }
        if ($pending === 1) {
        }
        if ($pending === 2) {
            $college = College::whereIn('college_id', $arrayHolder)->with('user')->first();
            $item = User::where("id", $college->user->id)->get();
        }
        if ($pending === 3) {
            $department = Department::whereIn('department_id', $arrayHolder)->with('user')->first();
            $item = User::where("id", $department->user->id)->get();
        }
        if ($pending === 4) {
            $sector = Sector::whereIn('sector_id', $arrayHolder)->with('user')->first();
            $item = User::where("id", $sector->user->id)->get();
        }

        if ($pending === 5) {
            $ipoStaff = UserRole::where('role_id', 8)->with('user')->get();
            foreach ($ipoStaff as $stafRole) array_push($stafRole->user->id);
            $item = User::whereIn("id", $userIdHolder)->get();
        }

        return $item;
    }
}
