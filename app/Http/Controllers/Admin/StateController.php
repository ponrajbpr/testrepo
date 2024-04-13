<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function allStates(){
        $users = \App\Models\User::join('state_details', 'users.id', '=', 'state_details.user_id')
            ->where('users.role', 4)
            ->where('users.isactive',1)
            ->select('users.*', 'state_details.*','users.status as userstatus')
            ->get();
        return view('admin.states.allstates', compact('users'));
    }
    public function approvedStates(){
        $users = \App\Models\User::join('state_details', 'users.id', '=', 'state_details.user_id')
            ->where('users.role', 4)
            ->where('users.status', 1)
            ->where('users.isactive',1)
            ->select('users.*', 'state_details.*','users.status as userstatus')
            ->get();
        return view('admin.states.approvedstates', compact('users'));
    }
    public function pendingStates(){
        $users = \App\Models\User::join('state_details', 'users.id', '=', 'state_details.user_id')
            ->where('users.role', 4)
            ->where('users.status', 0)
            ->where('users.isactive',1)
            ->select('users.*', 'state_details.*','users.status as userstatus')
            ->get();
        return view('admin.states.pendingstates', compact('users'));
    }

    public function rejectedStates(){
        $users = \App\Models\User::join('state_details', 'users.id', '=', 'state_details.user_id')
            ->where('users.role', 4)
            ->where('users.status', 2)
            ->where('users.isactive',1)
            ->select('users.*', 'state_details.*','users.status as userstatus')
            ->get();
        return view('admin.states.rejectedstates', compact('users'));
    }

    public function blockedStates(){
        $users = \App\Models\User::join('state_details', 'users.id', '=', 'state_details.user_id')
            ->where('users.role', 4)
            ->where('users.status', 4)
            ->where('users.isactive',1)
            ->select('users.*', 'state_details.*','users.status as userstatus')
            ->get();
        return view('admin.states.blockedstates', compact('users'));
    }

    public function deleteState($id){
        $user = \App\Models\User::find($id);
        $user->status = 5;
        $user->save();
        return redirect()->back()->with('success', 'State Deleted Successfully');
    }
    public function stateDetails($id){
        $user = \App\Models\User::join('state_details', 'users.id', '=', 'state_details.user_id')
        ->where('users.role', 4)
        ->where('users.id', $id)
        ->select('users.*', 'state_details.*','users.status as userstatus')
        ->first();
        return view('admin.states.statedetails', compact('user','id'));
    }
    public function approveState($id){
        try {
            \DB::beginTransaction();
            $user = \App\Models\User::join('state_details', 'users.id', '=', 'state_details.user_id')
                ->where('users.role', 4)
                ->where('users.id', $id)
                ->select('users.*', 'state_details.*')
                ->first();



            if (!$user) {
                // User not found
                return response()->json(['status' => 'error', 'message' => 'State not found'], 404);
            }

            $seq = \App\Models\Users::where('role', 4)->max('sequence') + 1;

            if($user->username != ''){
                if ($user->userstatus != 1) {
            $users= \App\Models\User::find($id);
            $users->status = 1;
            $association = \App\Models\MAssociation::find($user->association);
            $uid = "BFI" . $association->statecode . "S" . $seq;
            \Log::info($uid);
            $users->username = $uid;
            $users->sequence = $seq;
            $users->approved_by = \Auth::user()->id;
            $users->save();

            $details = StateDetails::where('user_id', $id)->first();
            $details->status = 1;
            $details->save();
            \DB::commit();

            $pass = EncryptedData::where('user_id', $id)->first();
            $upassword = decrypt($pass->value);
                }
            if ($users) {
                $user = $uid; // Replace this with your logic to get the user
                $password = $upassword; // Replace this with your logic to get the password

                Mail::to($user->email)->send(new StateApprovedNotification($user, $password));
                return response()->json(['status' => 'success', 'message' => 'State Approved Successfully']);
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

    public function rejectState($id,$reason){

        $user = \App\Models\User::join('state_details', 'users.id', '=', 'state_details.user_id')
            ->where('users.role', 4)
            ->where('users.id', $id)
            ->select('users.*', 'state_details.*')
            ->first();

        if (!$user) {
            // User not found
            return response()->json(['status' => 'error', 'message' => 'State not found'], 404);
        }

        $users= \App\Models\User::find($id);
        $users->status = 2;
        $users->reason = $reason;
        $users->rejected_by = \Auth::user()->id;
        $users->save();
        if ($users) {
            return response()->json(['status' => 'success', 'message' => 'State Rejected Successfully']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }

    }
    public function blockState($id){
        $user = \App\Models\User::find($id);
        $user->status = 3;
        $user->save();
        if($user){
            return response()->json(['status' => 'success', 'message' => 'State Blocked Successfully']);
        }else{
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
}
