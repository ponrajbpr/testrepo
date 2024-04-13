<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use App\Models\User;
use App\Mail\AdminRejectMail;
use App\Models\EncryptedData;
use App\Mail\AdminApproveMail;
use Illuminate\Support\Facades\Mail;

class ClubController extends Controller
{
    public function allClubs(){
        $users = \App\Models\User::join('club_details', 'users.id', '=', 'club_details.user_id')
            ->where('users.role', 7)
            ->where('users.status', '!=', 5)
            ->where('users.isactive',1)
            ->select('users.*', 'club_details.*','users.status as userstatus')
            ->get();
        return view('admin.club.allacademys', compact('users'));
    }

    public function approvedClubs(){
        $users = \App\Models\User::join('club_details', 'users.id', '=', 'club_details.user_id')
            ->where('users.role', 7)
            ->where('users.status', 1)
            ->where('users.isactive',1)
            ->select('users.*', 'club_details.*','users.status as userstatus')
            ->get();
        return view('admin.club.approvedacademys', compact('users'));
    }
    public function pendingClubs(){
        $users = \App\Models\User::join('club_details', 'users.id', '=', 'club_details.user_id')
            ->where('users.role', 7)
            ->where('users.status', 0)
            ->where('users.isactive',1)
            ->select('users.*', 'club_details.*','users.status as userstatus')
            ->get();
        return view('admin.club.pendingacademys', compact('users'));
    }

    public function rejectedClubs(){
        $users = \App\Models\User::join('club_details', 'users.id', '=', 'club_details.user_id')
            ->where('users.role', 7)
            ->where('users.status', 2)
            ->where('users.isactive',1)
            ->select('users.*', 'club_details.*','users.status as userstatus')
            ->get();
        return view('admin.club.rejectedacademys', compact('users'));
    }

    public function blockedClubs(){
        $users = \App\Models\User::join('club_details', 'users.id', '=', 'club_details.user_id')
            ->where('users.role', 7)
            ->where('users.status', 4)
            ->where('users.isactive',1)
            ->select('users.*', 'club_details.*','users.status as userstatus')
            ->get();
        return view('admin.club.blockedacademys', compact('users'));
    }

    public function deleteClub($id){
        $user = \App\Models\User::find($id);
        $user->status = 5;
        $user->save();
        return redirect()->back()->with('success', 'Academy Deleted Successfully');
    }
    public function clubDetails($id){
        $user = \App\Models\User::join('club_details', 'users.id', '=', 'club_details.user_id')
        ->where('users.role', 7)
        ->where('users.id', $id)
        ->select('users.*', 'club_details.*','users.status as userstatus')
        ->first();
        return view('admin.club.clubdetails', compact('user','id'));
    }
    public function approveClub($id){
        try {
            \DB::beginTransaction();
            $user = \App\Models\User::join('club_details', 'users.id', '=', 'club_details.user_id')
            ->where('users.role', 7)
            ->where('users.id', $id)
            ->select('users.*', 'club_details.*')
            ->first();



            if (!$user) {
                // User not found
                return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            }

            $seq = \App\Models\Users::where('role', 7)->max('sequence') + 1;

            if ($user->gender == 'Male') {
                $gcode = 'M';
            } else {
                $gcode = 'F';
            }

            if($user->username == ''){
            if ($user->status != 1) {

            $users= \App\Models\User::find($id);

            $users->status = 1;
            $association = \App\Models\MAssociation::find($user->association);
            $uid = "IRFU" . $association->statecode . $gcode . "A" . $seq;
            \Log::info($uid);
            $users->username = $uid;
            $users->sequence = $seq;
            $users->approved_by = \Auth::user()->id;
            $users->save();


            \DB::commit();


            $pass = EncryptedData::where('user_id', $id)->first();
            $upassword = decrypt($pass->value);
                }
            if ($users) {
                $username = $uid; // Replace this with your logic to get the username
                $password = $upassword; // Replace this with your logic to get the password

                // Create an instance of AdminApproveMail and pass username and password
                 $mail = new AdminApproveMail($username, $password);
                 Mail::to($user->email)->send($mail);

                return response()->json(['status' => 'success', 'message' => 'Academy Approved Successfully']);
            } else {

                return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
            }
            }
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            // Handle exceptions, log them, etc.
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }

    }

    public function rejectClub($id,$reason){

        $user = \App\Models\User::join('club_details', 'users.id', '=', 'club_details.user_id')
            ->where('users.role', 7)
            ->where('users.id', $id)
            ->select('users.*', 'club_details.*','users.status as userstatus')
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
            return response()->json(['status' => 'success', 'message' => 'Academy Rejected Successfully']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }

    }
    public function blockClub($id){
        $user = \App\Models\User::find($id);
        $user->status = 3;
        $user->save();
        if($user){
            return response()->json(['status' => 'success', 'message' => 'Academy Blocked Successfully']);
        }else{
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }

}
