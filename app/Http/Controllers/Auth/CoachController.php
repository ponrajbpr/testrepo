<?php

namespace App\Http\Controllers\Auth;

use Log;
use App\Models\Users;
use Illuminate\Support\Str;
use App\Models\CoachDetails;
use Illuminate\Http\Request;
use App\Models\EncryptedData;
use App\Models\CoachQualification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class CoachController extends Controller
{
    public function register(){
        return view('auth.coach.register');
    }
    public function store(Request $request){
          \Log::info($request->all());
          \Log::info("Entered the USers Table");
          $password = Str::random(8);
          $dob = sprintf('%s-%s-%s', $request->year, $request->month, $request->day);
          $user = new Users();
          $user->name = $request->firstname.' '.$request->lastname;
          $user->role = 3;
          $user->email = $request->email;
          $user->mobile = $request->mobile;
          $user->password = Hash::make($password);
          $user->status = 0;
          $user->save();

          $enc = new EncryptedData();
        $enc->user_id = $user->id;
        $enc->value = encrypt($password);
        $enc->save();

          if ($request->hasFile('photo')) {
            $photo = $this->uploadFile($request->file('photo'), 'IRFU/photo');
        }

        if ($request->hasFile('dob_proof')) {
            $dobProof = $this->uploadFile($request->file('dob_proof'), 'IRFU/dob_proof');
        }

        if ($request->hasFile('address_proof')) {
            $addressProof = $this->uploadFile($request->file('address_proof'), 'IRFU/address_proof');
        }

        if ($request->hasFile('qualification_file')) {
            $qualification_file = $this->uploadFile($request->file('qualification_file'), 'IRFU/qualification_file');
        }

        $details = new CoachDetails();
        $details->user_id = $user->id;
        $details->first_name = $request->firstname;
        $details->last_name = $request->lastname;
        $details->father_name = $request->father_name;
        $details->email = $request->email;
        $details->mobile = $request->mobile;
        $details->dob = $dob;
		$details->nsrs_no = $request->nsrs_no;
        $details->gender = $request->gender;
        $details->aadhar = $request->aadhar;
        $details->pan = $request->pan;
        $details->blood_group = $request->blood_group;
        $details->address1 = $request->address1;
        $details->address2 = $request->address2;
        $details->city = $request->city;
        $details->state = $request->state;
        $details->pincode = $request->pincode;
        $details->district = $request->district;
        $details->caddress1 = $request->caddress1;
        $details->caddress2 = $request->caddress2;
        $details->ccity = $request->ccity;
        $details->cstate = $request->cstate;
        $details->cpincode = $request->cpincode;
        $details->cdistrict = $request->cdistrict;
        $details->association = $request->association;
        $details->bank_name = $request->bank_name;
        $details->account_no = $request->account_number;
        $details->ifsc_code = $request->ifsc;
        $details->bank_branch = $request->branch;
        $details->photo = $photo;
        $details->dob_proof = $dobProof;
        $details->address_proof = $addressProof;
        $details->status = 0;
        $details->created_by = $user->id;
        $details->save();

        $qualification = new CoachQualification();
        $qualification->user_id = $user->id;
        $qualification->official_type = $request->official_type;
        $qualification->place_of_coaching = $request->place_of_coaching;
        $qualification->certification_passing = $request->certification_passing;
        $qualification->date_of_certificate = $request->date_of_certificate;
        $qualification->qualification_file = $qualification_file;
        $qualification->status = 1;
        $qualification->save();
        if($details){
            return redirect()->route('coach.register.success');
        }
    }
    private function uploadFile($file, $folder){
        $ext = $file->getClientOriginalExtension();
        $filename = "coach_" . rand(11111, 99999) . "_" . time() . '.' . $ext;
        $s3Path = $folder . '/' . $filename;
        Log::info($s3Path);
        \Storage::disk('s3')->put($s3Path, file_get_contents($file));

        return $filename;
        }

        public function success(){

            return view('auth.coach.success');
        }
}

