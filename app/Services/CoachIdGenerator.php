<?php
// IdCardGenerator.php
namespace App\Services;


use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Storage;
use App\Models\Users;
use App\Models\CoachDetails;

class CoachIdGenerator
{
    public function generateIdCard($user)
    {

        $user = Users::where('id', $user->id)->first();
        //$udetails = $user->details;
        $udetails = CoachDetails::where('user_id', $user->id)->first();

        $qrCodePath = $this->generateQrCode($user);

        $frontPath = $this->generateFrontIdCard($user, $udetails);
        $backPath = $this->generateBackIdCard($user, $udetails, $qrCodePath);

        return ['front' => $frontPath, 'back' => $backPath];
    }

    protected function generateQrCode($user)
    {
//         $user->load('details');
// $udetails = $user->details;
$user = Users::where('id', $user->id)->first();
//$udetails = $user->details;
$udetails = CoachDetails::where('user_id', $user->id)->first();

// Encode username in the QR code data
$qrData = [
    'username' => $user->username,
    // Add other data as needed
];

$qrname = $user->username . '.png';
$qrimage = QrCode::format('png')
    ->margin(1)
    ->merge(Storage::disk('s3')->url('IRFU/photo/' . $udetails->photo), 0.3, true)
    ->size(500)
    ->errorCorrection('H')
    ->generate(json_encode($qrData)); // Encode the data as JSON

$s3Path = 'IRFU/qrcode/' . $qrname;
Storage::disk('s3')->put($s3Path, $qrimage);

// Update the user with the QR code file name
$user->update(['qrcode' => $qrname]);
\Log::info($s3Path);
return $s3Path;
    }

    protected function generateFrontIdCard($user, $udetails)
    {
        if (date("d-m-Y", strtotime($udetails->dob)) == '01-01-1970') {
            $dob = "";
        } else {
            $dob = date("d-m-Y", strtotime($udetails->dob));
        }

        $background = Image::make(public_path('idfront.jpg'));
        // Add text to the image
        $background->text($user->name, 585, 320, function($font) {
        $font->file(public_path('fonts/AlumniSans-Bold.ttf'));
        $font->size(65);
        $font->color('#223c87');
        $font->align('center');
        $font->valign('top');
        });

        $background->text($dob, 550, 390, function($font) {
            $font->file(public_path('fonts/AlumniSans-Bold.ttf'));
            $font->size(45);
            $font->color('#223c87');
            $font->align('center');
            $font->valign('top');
            });

        $background->text($udetails->gender, 930, 390, function($font) {
            $font->file(public_path('fonts/AlumniSans-Bold.ttf'));
            $font->size(45);
            $font->color('#223c87');
            $font->align('center');
            $font->valign('top');
            });

        $background->text($user->username, 750, 468, function($font) {
            $font->file(public_path('fonts/AlumniSans-Bold.ttf'));
            $font->size(50);
            $font->color('#223c87');
            $font->align('center');
            $font->valign('top');
            });

        // Load an overlay image (e.g., a logo)
        $overlay = Image::make(Storage::disk('s3')->url('IRFU/photo/' . $udetails->photo));

        // Resize the overlay image if needed
        $overlay->resize(285, 305);

        // Insert the overlay image onto the background
        $background->insert($overlay, 'top-left', 40, 320);

        // Save the manipulated image to a temporary file
        $tempPath = storage_path('app/public/idfront/' . $user->id .'_'.time(). '_front.jpg');
        $frontname = $user->username. '_front.jpg';
        $fronts3path = 'IRFU/idfront/' . $user->username. '_front.jpg';
        $background->save($tempPath);

    // Upload the manipulated image to S3
    \Storage::disk('s3')->put($fronts3path, file_get_contents($tempPath));

    // Optionally, you can delete the temporary file
    unlink($tempPath);

    $update = Users::where('id', $user->id)->update(['idfront' => $frontname]);

        return $fronts3path;
    }

    protected function generateBackIdCard($user, $udetails, $qrCodePath)
    {
        $background1 = Image::make(public_path('idback.jpg'));

        // Define the font properties
$fontPath = public_path('fonts/AlumniSans-Bold.ttf');
$fontSize = 50;
$fontColor = '#223c87';

// Your long text
$longText = $udetails->address;

// Max width and height for the text box
$maxWidth = 300;
$maxHeight = 200;

// Word wrap the text
$wrappedText = wordwrap($longText, 20, "\n", false);

// Split the text into lines
$textLines = explode("\n", $wrappedText);

// Set the initial y-coordinate
$y = 60; // Adjust as needed

// Add each line of text to the image
foreach ($textLines as $line) {
    // Add the text to the image
    $background1->text($line, 300, $y, function ($font) use ($fontPath, $fontSize, $fontColor) {
        $font->file($fontPath);
        $font->size($fontSize);
        $font->color($fontColor);
        $font->align('left');
        $font->valign('top');
    });

    // Increment the y-coordinate for the next line
    $y += $fontSize + 5; // Adjust the spacing as needed
}

        if($user->qrcode == ''){
            $qrCodePath = $this->generateQrCode($user);
            $qrpath = Storage::disk('s3')->url($qrCodePath);
        }else{
            $qrpath = Storage::disk('s3')->url('IRFU/qrcode/' . $user->qrcode);
        }

        \Log::Info($qrpath."- Back id card");
    // Load an overlay image (e.g., a logo)
    $overlay = Image::make($qrpath);

    // Resize the overlay image if needed
    $overlay->resize(230, 240);

    // Insert the overlay image onto the background
    $background1->insert($overlay, 'top-left', 726, 320);

    // Save the manipulated image to a temporary file
    $tempPath1 = storage_path('app/public/idback/' . $user->id .'_'.time(). '_back.jpg');
    $backname = $user->username. '_back.jpg';
    $backs3path = 'IRFU/idback/' . $user->username. '_back.jpg';
    $background1->save($tempPath1);

// Upload the manipulated image to S3
\Storage::disk('s3')->put($backs3path, file_get_contents($tempPath1));

// Optionally, you can delete the temporary file
unlink($tempPath1);

$update = Users::where('id', $user->id)->update(['idback' => $backname]);

        return $backs3path;
    }
}
