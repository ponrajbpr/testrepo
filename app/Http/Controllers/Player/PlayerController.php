<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDetails;
use Illuminate\Support\Facades\Response;
use App\Services\IdCardGenerator;
use ZanySoft\LaravelPDF\PDF;
use DB;
use Carbon\Carbon;
use Image;
use Storage;
use QrCode;

class PlayerController extends Controller
{
	protected $idCardGenerator;

    public function __construct(IdCardGenerator $idCardGenerator)
    {
        $this->idCardGenerator = $idCardGenerator;
    }
    public function dashboard(){
        $user = auth()->user();
        $user->load('details'); // Eager load the details relationship
            return view('player.dashboard', compact('user'));

    }
	public function idcard(){
        $user = auth()->user();
        $user->load('details'); // Eager load the details relationship
        $udetails = UserDetails::where('user_id', $user->id)->first();
        
        // Generate ID card
        $idCardPaths = $this->idCardGenerator->generateIdCard($user);

        //dd($idCardPaths);
if($user->missingfields != 1){
            return view('player.missingfields', compact('user','udetails'));
        }else{
        return view('player.idcard', compact('user', 'udetails', 'idCardPaths'));
		}
    }
    public function generateIdCardPdf()
{
    $user = auth()->user();
    $user->load('details');
    $udetails = UserDetails::where('user_id', $user->id)->first();

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
    $pdfName = 'idcard_' . $user->id . '_' . time() . '.pdf';

    // Save the PDF to a file
    //$pdf->save(storage_path('pdf/' . $pdfName));
    return $pdf->stream($pdfName);
    // Return a download response
    //return response()->download(storage_path('pdf/' . $pdfName))->deleteFileAfterSend(true);
}
}
