<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Models\User;
use App\Models\CoachDetails;
use Illuminate\Http\Request;
use App\Mail\AdminRejectMail;
use App\Models\EncryptedData;
use App\Mail\AdminApproveMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class CoachController extends Controller
{
    public function allCoaches(){
        $users = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.status', '!=', 5)
            ->where('users.isactive',1)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->get();
        return view('admin.coaches.allcoaches', compact('users'));
    }

    public function approvedCoaches(){
        $users = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.status', 1)
            ->where('users.isactive',1)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->get();
        return view('admin.coaches.approvedcoaches', compact('users'));
    }
    public function pendingCoaches(){
        $users = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.status', 0)
            ->where('users.isactive',1)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->get();
        return view('admin.coaches.pendingcoaches', compact('users'));
    }

    public function rejectedCoaches(){
        $users = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.status', 2)
            ->where('users.isactive',1)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->get();
        return view('admin.coaches.rejectedcoaches', compact('users'));
    }

    public function blockedCoaches(){
        $users = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.status', 4)
            ->where('users.isactive',1)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->get();
        return view('admin.coaches.blockedcoaches', compact('users'));
    }

    public function deleteCoach($id){
        $user = \App\Models\User::find($id);
        $user->status = 5;
        $user->save();
        return redirect()->back()->with('success', 'Coach Deleted Successfully');
    }
    public function coachDetails($id){
        $user = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
        ->where('users.role', 3)
        ->where('users.id', $id)
        ->select('users.*', 'coach_details.*','users.status as userstatus')
        ->first();
        return view('admin.coaches.coachdetails', compact('user','id'));
    }
    public function approveCoach($id){
        try {
            \DB::beginTransaction();
            $user = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
                ->where('users.role', 3)
                ->where('users.id', $id)
                ->select('users.*', 'coach_details.*','users.status as userstatus')
                ->first();



            if (!$user) {
                // User not found
                return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            }

            $seq = \App\Models\Users::where('role', 3)->max('sequence') + 1;

            if ($user->gender == 'Male') {
                $gcode = 'M';
            } else {
                $gcode = 'F';
            }
            if($user->username == ''){
                if ($user->userstatus != 1) {
            $users= \App\Models\User::find($id);
            $users->status = 1;
            $association = \App\Models\MAssociation::find($user->association);
            $uid = "IRFU" . $association->statecode . $gcode . "C" . $seq;
            \Log::info($uid);
            $users->username = $uid;
            $users->sequence = $seq;
            $users->approved_by = \Auth::user()->id;
            $users->save();

            // $details = new CoachDetails;
            // $details->user_id = $id;
            // $details->status = 1;
            // $details->save();
            \DB::commit();
                }
                $pass = EncryptedData::where('user_id', $id)->first();
                $upassword = decrypt($pass->value);
            if ($users) {
                $username = $uid; // Replace this with your logic to get the username
                $password = $upassword; // Replace this with your logic to get the password

                // Create an instance of AdminApproveMail and pass username and password
                $mail = new AdminApproveMail($username, $password);
                Mail::to($user->email)->send($mail);
                return response()->json(['status' => 'success', 'message' => 'Coach Approved Successfully']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            // Handle exceptions, log them, etc.
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }

    }

    public function rejectCoach($id,$reason){

        $user = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.id', $id)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->first();

        if (!$user) {
            // User not found
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $users= \App\Models\User::find($id);
        $users->status = 2;
        $users->reason = $reason;
        $users->rejected_by = Auth::user()->id;
        $users->save();
        if ($users) {
            $mail = new AdminRejectMail($reason);
                Mail::to($user->email)->send($mail);
            return response()->json(['status' => 'success', 'message' => 'Coach Rejected Successfully']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }

    }
    public function blockCoach($id){
        $user = \App\Models\User::find($id);
        $user->status = 3;
        $user->save();
        if($user){
            return response()->json(['status' => 'success', 'message' => 'Coach Blocked Successfully']);
        }else{
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
}
