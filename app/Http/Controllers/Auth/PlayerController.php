<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\UserDetails;
use Illuminate\Support\Facades\Hash;
use App\Models\EncryptedData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Notifications\PlayerRegistrationNotification;
use App\Mail\UserRegistrationAcknowledgment;

class PlayerController extends Controller
{
    public function register(){

        return view('auth.player.register');
    }
    public function store(Request $request){
        \Log::info($request->all());
        $password = Str::random(8);
        \Log::info("Entered the USers Table");
        $dob = sprintf('%s-%s-%s', $request->year, $request->month, $request->day);
        $user = new Users();
        $user->name = $request->firstname.' '.$request->lastname;
        $user->role = 2;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->password = Hash::make($password);
        $user->status = 0;
        $user->save();

        $enc = new EncryptedData();
        $enc->user_id = $user->id;
        $enc->value = encrypt($password);
        $enc->save();
        $photo = '';
        $dobProof = '';
        $addressProof = '';
        if ($request->hasFile('photo')) {
            $photo = $this->uploadFile($request->file('photo'), 'IRFU/photo');
        }

        if ($request->hasFile('dob_proof')) {
            $dobProof = $this->uploadFile($request->file('dob_proof'), 'IRFU/dob_proof');
        }

        if ($request->hasFile('address_proof')) {
            $addressProof = $this->uploadFile($request->file('address_proof'), 'IRFU/address_proof');
        }

        $details = new UserDetails();
        $details->user_id = $user->id;
        $details->first_name = $request->firstname;
        $details->last_name = $request->lastname;
        $details->dob = date('Y-m-d', strtotime($dob));
        $details->gender = $request->gender;
        $details->mobile = $request->mobile;
        $details->email = $request->email;
        $details->aadhar = $request->aadhar;
        $details->blood_group = $request->blood_group;
        $details->nsrs_no = $request->nsrs_no;
        $details->height = $request->height;
        $details->weight = $request->weight;
        $details->father_name = $request->father_name;
        $details->father_mobile = $request->father_mobile;
        $details->address = $request->address;
        $details->caddress = $request->caddress;
        $details->pincode = $request->pincode;
        $details->cpincode = $request->cpincode;
        $details->association = $request->association;
        $details->associated_club = $request->associated_club;
        $details->bank_name = $request->bank_name;
        $details->bank_branch = $request->branch;
        $details->bank_account = $request->account_number;
        $details->bank_ifsc = $request->ifsc;
        $details->photo = $photo;
        $details->dob_proof = $dobProof;
        $details->address_proof = $addressProof;
        $details->created_by = $user->id;
        $details->save();
        $userName = $request->firstname.' '.$request->lastname;
        if($user->id){
            // Send the acknowledgment email
            Mail::to($request->email)->send(new UserRegistrationAcknowledgment($userName));
            // $details->notify(new PlayerRegistrationNotification($details));
            return redirect()->route('player.register.success');
        }
    }
    private function uploadFile($file, $folder){
    $ext = $file->getClientOriginalExtension();
    $filename = "player_" . rand(11111, 99999) . "_" . time() . '.' . $ext;
    $s3Path = $folder . '/' . $filename;
    \Storage::disk('s3')->put($s3Path, file_get_contents($file));

    return $filename;
    }
    public function success(){

        return view('auth.player.success');
    }
    public function getEmailCheck($id){

        $check = Users::where('email', $id)->first();
        if($check){
            return response()->json(['check' => 1]);
        }else{
            return response()->json(['check' => 0]);
        }


    }
    public function getMobileCheck($id){

        $check = Users::where('mobile', $id)->first();
        if($check){
            return response()->json(['check' => 1]);
        }else{
            return response()->json(['check' => 0]);
        }
    }
}
