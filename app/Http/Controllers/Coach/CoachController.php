<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoachDetails;
use DB;
use Image;
use Storage;
use QrCode;
use App\Models\Users;
use Illuminate\Support\Facades\Response;
use App\Services\CoachIdGenerator;
use ZanySoft\LaravelPDF\PDF;
use App\Models\CoachQualification;


class CoachController extends Controller
{
    protected $CoachIdGenerator;

    public function __construct(CoachIdGenerator $CoachIdGenerator)
    {
        $this->CoachIdGenerator = $CoachIdGenerator;
    }
    public function dashboard(){
        $user = auth()->user();
        $details = CoachDetails::where('user_id', $user->id)->first();
        return view('coach.dashboard', compact('user', 'details'));

    }

    public function idcard(){
        $user = auth()->user();
        $user->load('details'); // Eager load the details relationship
        $udetails = CoachDetails::where('user_id', $user->id)->first();

        // Generate ID card
        $idCardPaths = $this->CoachIdGenerator->generateIdCard($user);

        //dd($idCardPaths);

        return view('coach.idcard', compact('user', 'udetails', 'idCardPaths'));
    }
    public function generateIdCardPdf()
{
    $user = auth()->user();
    $user->load('details');
    $udetails = CoachDetails::where('user_id', $user->id)->first();

    // Generate ID card
    $idCardPaths = $this->CoachIdGenerator->generateIdCard($user);

    // View name for the PDF
    $viewName = 'coach.idcard_pdf';

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
