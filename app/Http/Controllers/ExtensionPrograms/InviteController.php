<?php

namespace App\Http\Controllers\ExtensionPrograms;

use App\Helpers\LogActivity;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Models\ExtensionTag;
use App\Models\ExtensionProgram;
use App\Models\ExtensionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ExtensionTagNotification;
use Illuminate\Support\Facades\DB;

class InviteController extends Controller
{
    public function index($id){

        $extension = ExtensionProgram::find($id);

        $coExtensionists = ExtensionTag::
                    where('extension_tags.extension_program_id', $extension->extension_program_id)
                    ->join('users', 'users.id', 'extension_tags.user_id')
                    ->select('extension_tags.id as invite_id', 'extension_tags.status as extension_status','users.*', 'extension_tags.is_owner')
                    ->get();

        //get Nature of involvement
        $involvement = [];
        foreach($coExtensionists as $row){
            if($row->extension_status == "1"){
                $temp = ExtensionProgram::where('user_id', $row->id)
                            ->where('extension_program_id', $extension->extension_program_id)
                            ->pluck('nature_of_involvement')->first();
                $involvement[$row->id] = $temp;
            }
        }

        $allEmployees = User::whereNotIn('users.id', (ExtensionTag::where('extension_program_id', $id)->pluck('user_id')->all()))->
                            where('users.id', '!=', auth()->id())->
                            select('users.*')->
                            get();
        
        return view('extension-programs.invite.index', compact('coExtensionists', 'allEmployees', 'extension', 'involvement'));
    }

    public function add($id, Request $request){

        $count = 0;

        $extension = ExtensionProgram::where('id', $id)->first();
        foreach($request->input('employees') as $row){
            ExtensionTag::create([
                'user_id' => $row,
                'sender_id' => auth()->id(),
                'extension_program_id' => $id,
                'extension_program_id' => $extension->extension_program_id
            ]);

            $user = User::find($row);
            $extension_title = "Extension";
            $sender = User::join('extension_programs', 'extension_programs.user_id', 'users.id')
                            ->where('extension_programs.user_id', auth()->id())
                            ->where('extension_programs.id', $id)
                            ->select('users.first_name', 'users.last_name', 'users.middle_name', 'users.suffix')->first();
            $url_accept = route('extension.invite.confirm', $id);
            $url_deny = route('extension.invite.cancel', $id);

            $notificationData = [
                'receiver' => $user->first_name,
                'title' => $extension_title,
                'sender' => $sender->first_name.' '.$sender->middle_name.' '.$sender->last_name.' '.$sender->suffix,
                'url_accept' => $url_accept,
                'url_deny' => $url_deny,
                'date' => date('F j, Y, g:i a'),
                'type' => 'ext-invite'
            ];

            Notification::send($user, new ExtensionTagNotification($notificationData));
            $count++;
        }
        LogActivity::addToLog('Had added '.$count.' extension partners in an extension program/project/activity.');

        return redirect()->route('extension.invite.index', $id)->with('success', count($request->input('employees')).' people invited as extension partner/s.');
    }

    public function confirm($id, Request $request){

        $user = User::find(auth()->id());

        LogActivity::addToLog('Had confirmed as an extension partner in an extension program/project/activity.');

        $user->notifications->where('id', $request->get('id'))->markAsRead();
        
        return redirect()->route('extension.code.create', ['extension_program_id' => $id, 'id' => $request->get('id') ])->with('info', 'Fill in your Nature of Involvement and Department where to commit the extension.');;
    }
    
    public function cancel($id , Request $request){
        $user = User::find(auth()->id());

        ExtensionTag::where('extension_program_id', $id)->where('user_id', auth()->id())->update([
            'status' => 0
        ]);

        $user->notifications->where('id', $request->get('id'))->markAsRead();
        DB::table('notifications')
            ->where('id', $request->get('id'))
            ->delete();
        
        LogActivity::addToLog('Had denied as an extension partner in an extension program/project/activity.');

        return redirect()->route('extension-programs.index')->with('success', 'Tagged extension has been successfully removed.');
    }

    public function remove($id, Request $request){
        $extension = ExtensionProgram::find($id);

        if(ExtensionProgram::where('user_id', $request->input('user_id'))->where('extension_program_id', $extension->extension_program_id)->exists()){
            $coESID = ExtensionProgram::where('extension_program_id', $extension->extension_program_id)->where('user_id', $request->input('user_id'))->pluck('id')->first();
            if(Report::where('report_reference_id', $coESID)->where('report_category_id', 12)->where('user_id', $request->input('user_id'))->exists()){
                return redirect()->route('extension.invite.index', $id)->with('error', 'Cannot do this action given that the person has already submitted the extension.');
            }

            ExtensionProgram::where('user_id', $request->input('user_id'))->where('extension_program_id', $extension->extension_program_id)->delete();

            ExtensionTag::where('extension_program_id', $id)->where('user_id', $request->input('user_id'))->delete();

            return redirect()->route('extension.invite.index', $id)->with('success', 'Action successful.');

        }
        
        ExtensionTag::where('extension_program_id', $id)->where('user_id', $request->input('user_id'))->delete();

        LogActivity::addToLog('Extensionists removed.');

        
        return redirect()->route('extension.invite.index', $id)->with('success', 'Sending confirmation for extension partner has been cancelled.');
    }
}
