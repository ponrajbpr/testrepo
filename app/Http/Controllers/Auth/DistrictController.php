<?php

namespace App\Http\Controllers\Auth;

use Log;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Models\EncryptedData;
use App\Models\DistrictDetails;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class DistrictController extends Controller
{
    public function register(){
        return view('auth.district.register');
    }
    public function store(Request $request)
    {
        \Log::info($request->all());
        \Log::info("Entered the USers Table");
        $password = Str::random(8);
        $user = new Users();
        $user->name = $request->association;
        $user->role = 8;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->password = Hash::make($password);
        $user->status = 0;
        $user->save();

        $enc = new EncryptedData();
      $enc->user_id = $user->id;
      $enc->value = encrypt($password);
      $enc->save();

        if ($request->hasFile('association_logo')) {
          $association_logo = $this->uploadFile($request->file('association_logo'), 'IRFU/association_logo');
      }

      if ($request->hasFile('certificate_state')) {
        $certificate_state = $this->uploadFile($request->file('certificate_state'), 'IRFU/certificate_state');
    }

      $details = new DistrictDetails();
      $details->user_id = $user->id;
	  $details->district_name = $request->district_name;
      $details->association = $request->association;
      $details->short_code = $request->short_code;
      $details->date_incorporation = $request->date_incorporation;
      $details->email = $request->email;
      $details->mobile = $request->mobile;
      $details->last_election = $request->last_election;
      $details->president_name = $request->president_name;
      $details->secretary_name = $request->secretary_name;
      $details->secretary_mobile = $request->secretary_mobile;
      $details->secemail = $request->secemail;
      $details->treasurer_name = $request->treasurer_name;
      $details->treasurer_mobile = $request->treasurer_mobile;
      $details->treasurer_email = $request->treasurer_email;
      $details->register_address1 = $request->register_address1;
      $details->register_address2 = $request->register_address2;
      $details->city = $request->city;
      $details->state = $request->state;
      $details->pincode = $request->pincode;
      $details->district = $request->district;
      $details->country = $request->country;
      $details->association_logo = $association_logo;
      $details->certificate_state = $certificate_state;
      $details->status = 0;
      $details->created_by = $user->id;
      $details->save();

      if($details){
          return redirect()->route('district.register.success');
      }

    }
    private function uploadFile($file, $folder){
        $ext = $file->getClientOriginalExtension();
        $filename = "district_" . rand(11111, 99999) . "_" . time() . '.' . $ext;
        $s3Path = $folder . '/' . $filename;
        Log::info($s3Path);
        \Storage::disk('s3')->put($s3Path, file_get_contents($file));

        return $filename;
        }

        public function success(){

            return view('auth.district.success');
        }

}
