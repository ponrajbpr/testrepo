<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use App\Models\EncryptedData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\UserDetails;
use App\Models\CoachDetails;
use App\Models\OfficialDetails;
use App\Models\StateDetails;

class DashboardController extends Controller
{
    public function dashboard(){
        $allplayer = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.isactive',1)
            ->select('users.*', 'users_details.*','users.status as userstatus')
            ->count();
        $approvedplayer = Users::where('role', 2)->where('status', 1)->where('isactive',1)->count();
        $pendingplayer = $users = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.status', 0)
            ->where('users.isactive',1)
            ->select('users.*', 'users_details.*','users.status as userstatus')
            ->count();
        $rejectedplayer = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.status', 2)
            ->where('users.isactive',1)
            ->select('users.*', 'users_details.*','users.status as userstatus')
            ->count();
        $blockedplayer = \App\Models\User::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.status', 4)
            ->where('users.isactive',1)
            ->select('users.*', 'users_details.*','users.status as userstatus')
            ->count();

        $allcoaches = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.status', '!=', 5)
            ->where('users.isactive',1)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->count();
        $approvedcoaches = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.status', 1)
            ->where('users.isactive',1)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->count();
        $pendingcoaches = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.status', 0)
            ->where('users.isactive',1)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->count();
        $rejectedcoaches = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.status', 2)
            ->where('users.isactive',1)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->count();
        $blockedcoaches = \App\Models\User::join('coach_details', 'users.id', '=', 'coach_details.user_id')
            ->where('users.role', 3)
            ->where('users.status', 4)
            ->where('users.isactive',1)
            ->select('users.*', 'coach_details.*','users.status as userstatus')
            ->count();

        $allclub = Users::where('role', 7)->where('isactive',1)->count();
        $approvedclub = Users::where('role', 7)->where('status', 1)->where('isactive',1)->count();
        $pendingclub = Users::where('role', 7)->where('status', 0)->where('isactive',1)->count();
        $rejectedclub = Users::where('role', 7)->where('status', 2)->where('isactive',1)->count();
        $blockedclub = Users::where('role', 7)->where('status', 3)->where('isactive',1)->count();

        $alldistricts = Users::where('role', 8)->where('isactive',1)->count();
        $approveddistricts = Users::where('role', 8)->where('status', 1)->where('isactive',1)->count();
        $pendingdistricts = Users::where('role', 8)->where('status', 0)->where('isactive',1)->count();
        $rejecteddistricts = Users::where('role', 8)->where('status', 2)->where('isactive',1)->count();
        $blockeddistricts = Users::where('role', 8)->where('status', 3)->where('isactive',1)->count();

        return view('admin/dashboard', compact('blockeddistricts','rejecteddistricts','pendingdistricts','approveddistricts','alldistricts','allplayer', 'approvedplayer', 'pendingplayer', 'rejectedplayer', 'allcoaches', 'approvedcoaches', 'pendingcoaches', 'rejectedcoaches', 'allclub', 'approvedclub', 'pendingclub', 'rejectedclub', 'blockedclub', 'blockedplayer', 'blockedcoaches'));
    }

    public function uploadPlayers(){
        return view('admin/players/uploadplayers');
    }

    public function uploadPlayersData(Request $request)
{

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('player_data'));

        $sheet = $spreadsheet->getActiveSheet();
        $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        for ($row = 2; $row <= count($allDataInSheet); $row++) {

            $name = trim($allDataInSheet[$row]["B"]);
            $state = trim($allDataInSheet[$row]["C"]);
            $gender = trim($allDataInSheet[$row]["D"]);
            $mobile = trim($allDataInSheet[$row]["E"]);
            $email = trim($allDataInSheet[$row]["F"]);
            $photo = trim($allDataInSheet[$row]["G"]);
            $height = trim($allDataInSheet[$row]["I"]);
            $weight = trim($allDataInSheet[$row]["J"]);
            $position = trim($allDataInSheet[$row]["H"]);

            $random_number = Str::random(8);
            $password = Hash::make($random_number);

            $users = new Users();
            $users->name = $name;
            $users->mobile = $mobile;
            $users->email = $email;
            $users->role = 2;
            $users->password = $password;
            $users->status = 0;
            $users->stateverify = 0;
            $users->save();

            $enc = new EncryptedData();
            $enc->user_id = $users->id;
            $enc->value = encrypt($random_number);
            $enc->save();

            $details = new UserDetails();
            $details->user_id = $users->id;
            $details->first_name = $name;
            $details->association = $state;
            $details->gender = $gender;
            $details->mobile = $mobile;
            $details->email = $email;
            $details->height = $height;
            $details->weight = $weight;
            $details->position = $position;
            $details->photo = $photo;
            $details->save();
        }

        return redirect()->route('admin/uploadplayers')->with('success', 'Players uploaded successfully');
    } catch (\Exception $e) {
        \Log::error($e->getMessage());
        return redirect()->route('admin/uploadplayers')->with('error', 'An error occurred during file upload.');
    }
}
public function uploadCoaches(){
    return view('admin/coaches/uploadcoaches');
}

public function uploadCoachesData(Request $request)
{

try {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('player_data'));

    $sheet = $spreadsheet->getActiveSheet();
    $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    for ($row = 2; $row <= count($allDataInSheet); $row++) {

        $name = trim($allDataInSheet[$row]["B"]);
        $state = trim($allDataInSheet[$row]["C"]);
        $gender = trim($allDataInSheet[$row]["D"]);
        $mobile = trim($allDataInSheet[$row]["E"]);
        $email = trim($allDataInSheet[$row]["F"]);
        $photo = trim($allDataInSheet[$row]["G"]);
        $height = trim($allDataInSheet[$row]["I"]);
        $weight = trim($allDataInSheet[$row]["J"]);
        $position = trim($allDataInSheet[$row]["H"]);

        $random_number = Str::random(8);
        $password = Hash::make($random_number);

        $users = new Users();
        $users->name = $name;
        $users->mobile = $mobile;
        $users->email = $email;
        $users->role = 3;
        $users->password = $password;
        $users->status = 0;
        $users->stateverify = 0;
        $users->save();

        $enc = new EncryptedData();
        $enc->user_id = $users->id;
        $enc->value = encrypt($random_number);
        $enc->save();

        $details = new CoachDetails();
        $details->user_id = $users->id;
        $details->first_name = $name;
        $details->association = $state;
        $details->gender = $gender;
        $details->mobile = $mobile;
        $details->email = $email;
        $details->photo = $photo;
        $details->save();
    }

    return redirect()->route('admin/uploadcoaches')->with('success', 'Coaches uploaded successfully');
} catch (\Exception $e) {
    \Log::error($e->getMessage());
    return redirect()->route('admin/uploadcoaches')->with('error', 'An error occurred during file upload.');
}
}

public function getApprovedExcel(){
    $users = Users::where('role', 2)->where('status', 1)->where('isactive', 1)->get();
    $excelData = [];

    foreach ($users as $user) {
        $id = $user->id;
        $user = Users::join('users_details', 'users.id', '=', 'users_details.user_id')
        ->where('users.role', 2)
        ->where('users.id', $id)
        ->select('users.*', 'users_details.*')
        ->first();
        $pass = EncryptedData::where('user_id', $id)->first();
        $upassword = decrypt($pass->value);
        $excelData[] = [
            'Name' => $user->name,
            'Email' => $user->email,
            'Mobile' => $user->mobile,
            'Gender' => $user->gender,
            'Username' => $user->username,
            'Password' => $upassword,
            // Add other user details as needed
        ];
    }
    // Create a new Spreadsheet
    $spreadsheet = new Spreadsheet();

    // Set the column headers
    $spreadsheet->getActiveSheet()->fromArray(
        ['Username', 'Password'],  // Add other headers as needed
        null,
        'A1'
    );

    // Add user data to the spreadsheet
    $spreadsheet->getActiveSheet()->fromArray(
        $excelData,
        null,
        'A2'
    );

    // Save the spreadsheet to a file
    $excelPath = storage_path('app/public/excel/approvedusers.xlsx');
    $writer = new Xlsx($spreadsheet);
    $writer->save($excelPath);

    // Return the Excel file as a response
    return response()->json(['status' => 'success', 'message' => 'Users Approved and Excel file created']);
}
public function autoApprovePlayer(){
    try {
        \DB::beginTransaction();

        $approvedUsers = \App\Models\Users::where('status', 0)->where('role', 2)->where('isactive', 1)->get();
        $excelData = [];

        foreach ($approvedUsers as $approvedUser) {
            $id = $approvedUser->id;
            \Log::info($id);
            $user = \App\Models\Users::join('users_details', 'users.id', '=', 'users_details.user_id')
            ->where('users.role', 2)
            ->where('users.id', $id)
            ->select('users.*', 'users_details.*','users.status as userstatus')
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
                if ($user->userstatus == 0) {
                    $users= \App\Models\User::find($id);
                    $users->status = 1;
                    $association = \App\Models\MAssociation::find($user->association);
                    $uid = "BFI" . $association->statecode . $gcode . "P" . $seq;
                    \Log::info($uid);
                    $users->username = $uid;
                    $users->sequence = $seq;
                    $users->approved_by = \Auth::user()->id;
                    $users->save();

                    $details = OfficialDetails::where('user_id', $id)->first();

                    if ($details) {
                    $details->update(['status' => 1]);
                    } else {
                       // Handle the case where no user details are found for the given user ID
                    }

                    \DB::commit();

                    $pass = EncryptedData::where('user_id', $id)->first();
                    $upassword = decrypt($pass->value);
                    \Log::Info("Credentail:".$uid."=>".$upassword);
                    // Add user details to Excel data array
                    $excelData[] = [
                        'Username' => $uid,
                        'Password' => $upassword,
                        // Add other user details as needed
                    ];
                }
            }

        }
        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet();

        // Set the column headers
        $spreadsheet->getActiveSheet()->fromArray(
            ['Username', 'Password'],  // Add other headers as needed
            null,
            'A1'
        );

        // Add user data to the spreadsheet
        $spreadsheet->getActiveSheet()->fromArray(
            $excelData,
            null,
            'A2'
        );

        // Save the spreadsheet to a file
        $excelPath = storage_path('excel/approvedusers.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($excelPath);

        \DB::commit();
        return response()->json(['status' => 'success', 'message' => 'Users Approved and Excel file created']);

    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error($e->getMessage());
        // Handle exceptions, log them, etc.
        return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
    }

}
public function autoApproveOfficial(){
    try {
        \DB::beginTransaction();

        $approvedUsers = \App\Models\Users::where('status', 0)->where('role', 6)->where('isactive', 1)->get();
        $excelData = [];

        foreach ($approvedUsers as $approvedUser) {
            $id = $approvedUser->id;
            \Log::info($id);
            $user = \App\Models\Users::join('official_details', 'users.id', '=', 'official_details.user_id')
            ->where('users.role', 6)
            ->where('users.id', $id)
            ->select('users.*', 'official_details.*','users.status as userstatus')
            ->first();

            if (!$user) {
            // User not found
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            }

            $seq = \App\Models\Users::where('role', 6)->max('sequence') + 1;

            if ($user->gender == 'Male') {
                $gcode = 'M';
            } else {
                $gcode = 'F';
            }
            if($user->username == ''){
                if ($user->userstatus == 0) {
                    $users= \App\Models\User::find($id);
                    $users->status = 1;
                    $association = \App\Models\MAssociation::find($user->association);
                    $uid = "BFI" . $association->statecode . $gcode . "R" . $seq;
                    \Log::info($uid);
                    $users->username = $uid;
                    $users->sequence = $seq;
                    $users->approved_by = \Auth::user()->id;
                    $users->save();

                    $details = OfficialDetails::where('user_id', $id)->first();

                    if ($details) {
                    $details->update(['status' => 1]);
                    } else {
                       // Handle the case where no user details are found for the given user ID
                    }

                    \DB::commit();

                    $pass = EncryptedData::where('user_id', $id)->first();
                    $upassword = decrypt($pass->value);
                    \Log::Info("Credentail:".$uid."=>".$upassword);
                    // Add user details to Excel data array
                    $excelData[] = [
                        'Username' => $uid,
                        'Password' => $upassword,
                        // Add other user details as needed
                    ];
                }
            }

        }
        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet();

        // Set the column headers
        $spreadsheet->getActiveSheet()->fromArray(
            ['Username', 'Password'],  // Add other headers as needed
            null,
            'A1'
        );

        // Add user data to the spreadsheet
        $spreadsheet->getActiveSheet()->fromArray(
            $excelData,
            null,
            'A2'
        );

        // Save the spreadsheet to a file
        $excelPath = storage_path('excel/approvedofficials.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($excelPath);

        \DB::commit();
        return response()->json(['status' => 'success', 'message' => 'Users Approved and Excel file created']);

    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error($e->getMessage());
        // Handle exceptions, log them, etc.
        return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
    }

}
public function uploadOfficials(){
    return view('admin/officials/uploadofficials');
}

public function uploadOfficialsData(Request $request)
{

try {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('player_data'));

    $sheet = $spreadsheet->getActiveSheet();
    $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    for ($row = 2; $row <= count($allDataInSheet); $row++) {

        $name = trim($allDataInSheet[$row]["B"]);
        $state = trim($allDataInSheet[$row]["C"]);
        $gender = trim($allDataInSheet[$row]["D"]);
        $mobile = trim($allDataInSheet[$row]["E"]);
        $email = trim($allDataInSheet[$row]["F"]);
        $photo = trim($allDataInSheet[$row]["G"]);


        $random_number = Str::random(8);
        $password = Hash::make($random_number);

        $users = new Users();
        $users->name = $name;
        $users->mobile = $mobile;
        $users->email = $email;
        $users->role = 6;
        $users->password = $password;
        $users->status = 0;
        $users->stateverify = 0;
        $users->save();

        $enc = new EncryptedData();
        $enc->user_id = $users->id;
        $enc->value = encrypt($random_number);
        $enc->save();

        $details = new OfficialDetails();
        $details->user_id = $users->id;
        $details->first_name = $name;
        $details->association = $state;
        $details->gender = $gender;
        $details->mobile = $mobile;
        $details->email = $email;
        $details->photo = $photo;
        $details->save();
    }

    return redirect()->route('admin/uploadofficials')->with('success', 'Officials uploaded successfully');
} catch (\Exception $e) {
    \Log::error($e->getMessage());
    return redirect()->route('admin/uploadofficials')->with('error', 'An error occurred during file upload.');
}
}

public function uploadStates(){
    return view('admin/states/uploadstates');
}

public function uploadStatesData(Request $request)
{

try {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('player_data'));

    $sheet = $spreadsheet->getActiveSheet();
    $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    for ($row = 2; $row <= count($allDataInSheet); $row++) {

        $name = trim($allDataInSheet[$row]["B"]);
        $state = trim($allDataInSheet[$row]["C"]);
        $gender = trim($allDataInSheet[$row]["D"]);
        $mobile = trim($allDataInSheet[$row]["E"]);
        $email = trim($allDataInSheet[$row]["F"]);
        $photo = trim($allDataInSheet[$row]["G"]);


        $random_number = Str::random(8);
        $password = Hash::make($random_number);

        $users = new Users();
        $users->name = $name;
        $users->mobile = $mobile;
        $users->email = $email;
        $users->role = 4;
        $users->password = $password;
        $users->status = 0;
        $users->stateverify = 0;
        $users->save();

        $enc = new EncryptedData();
        $enc->user_id = $users->id;
        $enc->value = encrypt($random_number);
        $enc->save();

        $details = new StateDetails();
        $details->user_id = $users->id;
        $details->association_name = $name;
        $details->association_id = $state;
        $details->save();
    }

    return redirect()->route('admin/uploadstates')->with('success', 'States uploaded successfully');
} catch (\Exception $e) {
    \Log::error($e->getMessage());
    return redirect()->route('admin/uploadstates')->with('error', 'An error occurred during file upload.');
}
}
public function autoApproveState(){
    try {
        \DB::beginTransaction();

        $approvedUsers = \App\Models\Users::where('status', 0)->where('role', 4)->where('isactive', 1)->get();
        $excelData = [];

        foreach ($approvedUsers as $approvedUser) {
            $id = $approvedUser->id;
            \Log::info($id);
            $user = \App\Models\Users::join('state_details', 'users.id', '=', 'state_details.user_id')
            ->where('users.role', 4)
            ->where('users.id', $id)
            ->select('users.*', 'state_details.*','users.status as userstatus')
            ->first();

            if (!$user) {
            // User not found
            return response()->json(['status' => 'error', 'message' => 'State not found'], 404);
            }

            $seq = \App\Models\Users::where('role', 4)->max('sequence') + 1;

            if($user->username == ''){
                if ($user->userstatus == 0) {
                    $users= \App\Models\User::find($id);
                    $users->status = 1;
                    $association = \App\Models\MAssociation::find($user->association_id);
                    $uid = "BFI" . $association->statecode . "S" . $seq;
                    \Log::info($uid);
                    $users->username = $uid;
                    $users->sequence = $seq;
                    $users->approved_by = \Auth::user()->id;
                    $users->save();


                    \DB::commit();

                    $pass = EncryptedData::where('user_id', $id)->first();
                    $upassword = decrypt($pass->value);
                    \Log::Info("Credentail:".$uid."=>".$upassword);
                    // Add user details to Excel data array
                    $excelData[] = [
                        'Username' => $uid,
                        'Password' => $upassword,
                        // Add other user details as needed
                    ];
                }
            }

        }
        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet();

        // Set the column headers
        $spreadsheet->getActiveSheet()->fromArray(
            ['Username', 'Password'],  // Add other headers as needed
            null,
            'A1'
        );

        // Add user data to the spreadsheet
        $spreadsheet->getActiveSheet()->fromArray(
            $excelData,
            null,
            'A2'
        );

        // Save the spreadsheet to a file
        $excelPath = storage_path('excel/approvedstates.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($excelPath);

        \DB::commit();
        return response()->json(['status' => 'success', 'message' => 'Users Approved and Excel file created']);

    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error($e->getMessage());
        // Handle exceptions, log them, etc.
        return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
    }

}

}
