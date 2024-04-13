<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\EncryptedData;
use App\Mail\AdminApproveMail;
use App\Mail\AdminRejectMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class DistrictController extends Controller
{
    public function allDistrict(){
        $users = \App\Models\User::join('district_details', 'users.id', '=', 'district_details.user_id')
            ->where('users.role', 8)
            ->where('users.isactive',1)
            ->select('users.*', 'district_details.*','users.status as userstatus')
            ->get();
        return view('admin.district.alldistrict', compact('users'));
    }
    public function approvedDistrict(){
        $users = \App\Models\User::join('district_details', 'users.id', '=', 'district_details.user_id')
            ->where('users.role', 8)
            ->where('users.status', 1)
            ->where('users.isactive',1)
            ->select('users.*', 'district_details.*','users.status as userstatus')
            ->get();
        return view('admin.district.approveddistrict', compact('users'));
    }
    public function pendingDistrict(){
        $users = \App\Models\User::join('district_details', 'users.id', '=', 'district_details.user_id')
            ->where('users.role', 8)
            ->where('users.status', 0)
            ->where('users.isactive',1)
            ->select('users.*', 'district_details.*','users.status as userstatus')
            ->get();
        return view('admin.district.pendingdistrict', compact('users'));
    }

    public function rejectedDistrict(){
        $users = \App\Models\User::join('district_details', 'users.id', '=', 'district_details.user_id')
            ->where('users.role', 8)
            ->where('users.status', 2)
            ->where('users.isactive',1)
            ->select('users.*', 'district_details.*','users.status as userstatus')
            ->get();
        return view('admin.district.rejecteddistrict', compact('users'));
    }

    public function blockedDistrict(){
        $users = \App\Models\User::join('district_details', 'users.id', '=', 'district_details.user_id')
            ->where('users.role', 8)
            ->where('users.status', 4)
            ->where('users.isactive',1)
            ->select('users.*', 'district_details.*','users.status as userstatus')
            ->get();
        return view('admin.district.blockeddistrict', compact('users'));
    }

    public function deleteDistrict($id){
        $user = \App\Models\User::find($id);
        $user->status = 5;
        $user->save();
        return redirect()->back()->with('success', 'district Deleted Successfully');
    }
    public function DistrictDetails($id){
        $user = \App\Models\User::join('district_details', 'users.id', '=', 'district_details.user_id')
        ->where('users.role', 8)
        ->where('users.id', $id)
        ->select('users.*', 'district_details.*','users.status as userstatus')
        ->first();
        return view('admin.district.districtdetails', compact('user','id'));
    }
    public function approveDistrict($id){
            try {
                \DB::beginTransaction();
                $user = \App\Models\User::join('district_details', 'users.id', '=', 'district_details.user_id')
                ->where('users.role', 8)
                ->where('users.id', $id)
                ->select('users.*', 'district_details.*')
                ->first();



                if (!$user) {
                    // User not found
                    return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
                }

                $seq = \App\Models\Users::where('role', 8)->max('sequence') + 1;

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
                $uid = "IRFU" . $association->statecode . $gcode . "D" . $seq;
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


    public function rejectDistrict($id,$reason){

        $user = \App\Models\User::join('district_details', 'users.id', '=', 'district_details.user_id')
            ->where('users.role', 8)
            ->where('users.id', $id)
            ->select('users.*', 'district_details.*')
            ->first();

        if (!$user) {
            // User not found
            return response()->json(['status' => 'error', 'message' => 'District not found'], 404);
        }

        $users= \App\Models\User::find($id);
        $users->status = 2;
        $users->reason = $reason;
        $users->rejected_by = \Auth::user()->id;
        $users->save();
        if ($users) {
            return response()->json(['status' => 'success', 'message' => 'District Rejected Successfully']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }

    }
    public function blockDistrict($id){
        $user = \App\Models\User::find($id);
        $user->status = 3;
        $user->save();
        if($user){
            return response()->json(['status' => 'success', 'message' => 'District Blocked Successfully']);
        }else{
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }

}
