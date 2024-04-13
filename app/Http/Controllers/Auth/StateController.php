<?php

namespace App\Http\Controllers\Auth;

use App\Models\Users;
use App\Models\StateDetails;
use Illuminate\Http\Request;
use App\Models\EncryptedData;
use App\Http\Controllers\Controller;

class StateController extends Controller
{
    public function register(){
        return view('auth.state.register');
    }
    public function store(Request $request){
        \Log::info($request->all());
        \Log::info("Entered the USers Table");
        $password = Str::random(8);
        $user = new Users();
        $user->name = $request->firstname.' '.$request->lastname;
        $user->role = 4;
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
          $photo = $this->uploadFile($request->file('association_logo'), 'IRFU/associationlogo');
      }

      if ($request->hasFile('registration_certificate')) {
          $dobProof = $this->uploadFile($request->file('registration_certificate'), 'IRFU/registration_certificate');
      }

      if ($request->hasFile('secretary_photo')) {
          $addressProof = $this->uploadFile($request->file('secretary_photo'), 'IRFU/secretary_photo');
      }
      $details = new StateDetails();
      $details->user_id = $user->id;
      $details->association_id = $request->association_id;
      $details->association_name = $request->association_name;
      $details->email = $request->email;
      $details->mobile = $request->mobile;
      $details->secretary_name = $request->secretary_name;
      $details->secretary_email = $request->secretary_email;
      $details->office_address1 = $request->office_address1;
      $details->office_address2 = $request->office_address2;
      $details->office_city = $request->office_city;
      $details->office_district = $request->office_district;
      $details->office_state = $request->office_state;
      $details->office_pincode = $request->office_pincode;
      $details->association_logo = $association_logo;
      $details->registration_certificate = $registration_certificate;
      $details->secratery_picture = $secretary_photo;
      $details->status = 0;
      $details->created_by = $user->id;
      $details->save();


      if($details){
          return redirect()->route('state.register.success');
      }


  }

  private function uploadFile($file, $folder){
      $ext = $file->getClientOriginalExtension();
      $filename = "state_" . rand(11111, 99999) . "_" . time() . '.' . $ext;
      $s3Path = $folder . '/' . $filename;
      \Log::info($s3Path);
      \Storage::disk('s3')->put($s3Path, file_get_contents($file));

      return $filename;
      }
      public function success(){
          return view('auth.state.success');
      }
}
