<?php

namespace App\Http\Controllers\Auth;
use Log;
use App\Models\Users;
use App\Models\ClubDetails;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\EncryptedData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ClubController extends Controller
{
    public function register(){
        return view('auth.club.register');
    }
    public function store(Request $request)
    {
        \Log::info($request->all());
        \Log::info("Entered the USers Table");
        $password = Str::random(8);
        $user = new Users();
        $user->name = $request->acadmey_name;
        $user->role = 7;
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

      if ($request->hasFile('academey_aadhar')) {
        $academeyaadhar = $this->uploadFile($request->file('academey_aadhar'), 'IRFU/academey_aadhar');
    }

    if ($request->hasFile('academey_photo')) {
        $academeyphoto = $this->uploadFile($request->file('academey_photo'), 'IRFU/academey_photo');
    }

    if ($request->hasFile('academey_logo')) {
        $academeylogo = $this->uploadFile($request->file('academey_logo'), 'IRFU/academey_logo');
    }

      $details = new ClubDetails();
      $details->user_id = $user->id;
      $details->acadmey_name = $request->acadmey_name;
	  $details->association = $request->association;
      $details->short_code = $request->short_code;
      $details->registered_certificate = $request->registered_certificate;
      $details->email = $request->email;
      $details->mobile = $request->mobile;
      $details->pan_no = $request->pan_no;
	  $details->nsrs_no = $request->nsrs_no;
      $details->practice_place = $request->practice_place;
      $details->number_of_players = $request->number_of_players;
      $details->caddress1 = $request->caddress1;
      $details->caddress2 = $request->caddress2;
      $details->ccity = $request->ccity;
      $details->cstate = $request->cstate;
      $details->cpincode = $request->cpincode;
      $details->cdistrict = $request->cdistrict;
      $details->club_director = $request->club_director;
      $details->club_mobile= $request->club_mobile;
      $details->club_email = $request->club_email;
      $details->club_aadhar = $request->club_aadhar;
      $details->photo = $photo;
      $details->academey_aadhar = $academeyaadhar;
      $details->academey_logo = $academeylogo;
      $details->academey_photo = $academeyphoto;
      $details->status = 0;
      $details->created_by = $user->id;
      $details->save();

      if($details){
          return redirect()->route('club.register.success');
      }

    }
    private function uploadFile($file, $folder){
        $ext = $file->getClientOriginalExtension();
        $filename = "club_" . rand(11111, 99999) . "_" . time() . '.' . $ext;
        $s3Path = $folder . '/' . $filename;
        Log::info($s3Path);
        \Storage::disk('s3')->put($s3Path, file_get_contents($file));

        return $filename;
        }

        public function success(){

            return view('auth.club.success');
        }
}
