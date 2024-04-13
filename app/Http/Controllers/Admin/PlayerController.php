<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\PlayerApprovedNotification;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Services\IdCardGenerator;
use Illuminate\Support\Str;
use ZanySoft\LaravelPDF\PDF;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Image;
use Storage;
use QrCode;
use App\Models\UserDetails;
use App\Models\EncryptedData;
use App\Mail\AdminApproveMail;
use App\Mail\AdminRejectMail;

class PlayerController extends Controller
{
    protected $idCardGenerator;

    public function __construct(IdCardGenerator $idCardGenerator)
    {
        $this->idCardGenerator = $idCardGenerator;
    }
    public function generateIdCardPdf($id)
{
    $user = \App\Models\Users::find($id);
    $user->load('details');
    $udetails = UserDetails::where('user_id', $id)->first();

    // Generate ID card
    $idCardPaths = $this->idCardGenerator->generateIdCard($user);

    // View name for the PDF
    $viewName = 'player.idcard_pdf';

    // Data to pass to the view
    $data = compact('user', 'udetails', 'idCardPaths');
    $pdf = new PDF();
    // Generate PDF
    $pdf->loadView($viewName, $data);

    // Set the PDF name for download
    $pdfName = 'idcard_' . $id . '_' . time() . '.pdf';

    // Save the PDF to a file
    //$pdf->save(storage_path('pdf/' . $pdfName));
    return $pdf->stream($pdfName);
    // Return a download response
    //return response()->download(storage_path('pdf/' . $pdfName))->deleteFileAfterSend(true);
}
    public function allPlayers(){
        $users = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.isactive',1)
            ->select('users.*', 'users_details.*','users.status as userstatus')
            ->get();
        return view('admin.players.allplayers', compact('users'));
    }

    public function approvedPlayers(){
        $users = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.status', 1)
            ->where('users.isactive',1)
            ->select('users.*', 'users_details.*','users.status as userstatus')
            ->get();
        return view('admin.players.approvedplayers', compact('users'));
    }
    public function pendingPlayers(){
        $users = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.status', 0)
            ->where('users.isactive',1)
            ->select('users.*', 'users_details.*','users.status as userstatus')
            ->get();
        return view('admin.players.pendingplayers', compact('users'));
    }

    public function rejectedPlayers(){
        $users = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.status', 2)
            ->where('users.isactive',1)
            ->select('users.*', 'users_details.*','users.status as userstatus')
            ->get();
        return view('admin.players.rejectedplayers', compact('users'));
    }

    public function blockedPlayers(){
        $users = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.status', 4)
            ->where('users.isactive',1)
            ->select('users.*', 'users_details.*','users.status as userstatus')
            ->get();
        return view('admin.players.blockedplayers', compact('users'));
    }

    public function deletePlayer($id){
        $user = \App\Models\User::find($id);
        $user->status = 5;
        $user->save();
        return redirect()->back()->with('success', 'Player Deleted Successfully');
    }
    public function playerDetails($id){
        $user = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
        ->where('users.role', 2)
        ->where('users.id', $id)
        ->select('users.*', 'users_details.*','users.status as userstatus')
        ->first();
        return view('admin.players.playerdetails', compact('user','id'));
    }
    public function approvePlayer($id){
        try {
            \DB::beginTransaction();
            $user = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
                ->where('users.role', 2)
                ->where('users.id', $id)
                ->select('users.*', 'users_details.*')
                ->first();



            if (!$user) {
                // User not found
                return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            }

            $seq = \App\Models\Users::where('role', 2)->max('sequence') + 1;

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
            $uid = "IRFU" . $association->statecode . $gcode . "P" . $seq;
            \Log::info($uid);
            $users->username = $uid;
            $users->sequence = $seq;
            $users->approved_by = \Auth::user()->id;
            $users->save();
            // $password = Str::random(8);
            // $users->username = $uid;
            // $user->password = Hash::make($password);
            // $users->sequence = $seq;
            // $users->approved_by = \Auth::user()->id;
            // $users->save();

            // $enc = EncryptedData::where('user_id', $id)->first();
            // $enc->user_id = $id;
            // $enc->value = encrypt($password);
            // $enc->save();

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

                return response()->json(['status' => 'success', 'message' => 'Player Approved Successfully']);
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



    public function rejectPlayer($id,$reason){

        $user = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.id', $id)
            ->select('users.*', 'users_details.*','users.status as userstatus')
            ->first();

        if (!$user) {
            // User not found
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $users= \App\Models\User::find($id);
        $users->status = 2;
        $users->reason = $reason;
        $users->rejected_by = \Auth::user()->id;
        $users->save();
        if ($users) {
            $mail = new AdminRejectMail($reason);
                Mail::to($user->email)->send($mail);
            return response()->json(['status' => 'success', 'message' => 'Player Rejected Successfully']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }

    }
    public function blockPlayer($id){
        $user = \App\Models\User::find($id);
        $user->status = 3;
        $user->save();
        if($user){
            return response()->json(['status' => 'success', 'message' => 'Player Blocked Successfully']);
        }else{
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }

    public function updatePasswordsByDate()
{
    $date = '02-12-2023';
    // Parse the date to ensure it's in the correct format
    $parsedDate = Carbon::parse($date)->format('Y-m-d');

    // Get users created on the specified date
    $users = Users::whereDate('created_at','>', $parsedDate)->where('status',1)->where('role',2)->select('id','username','email')->get();

    // Loop through the users and update their passwords
    foreach ($users as $user) {
        // Generate a new random password
        $password = Str::random(8);

        // Update the password in the users table
        $user->password = Hash::make($password);
        $user->flag = 1;
        $user->save();

        // Update the encrypted password in the encrypted_data table
        $enc = EncryptedData::where('user_id', $user->id)->first();

        if (!$enc) {
            $enc = new EncryptedData();
            $enc->user_id = $user->id;
        }

        $enc->value = encrypt($password);
        $enc->save();
        $username = $user->username; // Replace this with your logic to get the username

                // Create an instance of AdminApproveMail and pass username and password
                $mail = new AdminApproveMail($username, $password);
                Mail::to($user->email)->send($mail);
    }


    return redirect()->back()->with('success', 'Passwords updated successfully for users created on ' . $parsedDate);
}
}
