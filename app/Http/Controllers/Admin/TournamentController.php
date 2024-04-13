<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MState;
use App\Models\Tournaments;
use App\Models\TourAgeCategory;
use App\Models\MAgeCategory;
use Illuminate\Support\Facades\Log;
use App\Models\TourEntries;
use App\Models\TournamentsTeam;
use App\Models\TourCoaches;
use DB;

class TournamentController extends Controller
{
    public function addTournament()
    {
        $states = MState::all();
        return view('admin.tournaments.addtournaments', compact('states'));
    }
    public function viewEntry($id)
    {
        $tournament = Tournaments::find($id);
        $tourentry = TourEntries::join('tournaments', 'tournaments.id', '=', 'tournament_entries.tournament_id')
    ->join('massociations', 'massociations.id', '=', DB::raw('CAST(tournament_entries.state_id AS bigint)'))
    ->where('tournament_entries.tournament_id', $id)
    ->select('massociations.name as stateName', 'tournament_entries.*', 'tournaments.id as tourid')
    ->get();

        return view('admin.tournaments.viewentry',compact('tourentry','tournament'));
    }
    public function entryView($tid,$sid){
        $state_id = $sid;
        $tournament = Tournaments::find($tid);
        if($tid == 5){
            if($state_id == 10 || $state_id == 21 || $state_id == 33){
        $cat = TourAgeCategory::join('age_category', 'age_category.id', 'tournament_age_category.age_category')->where('tournament_id', $tid)->where('age_category', 8)->get();
        }else{
            $cat = TourAgeCategory::join('age_category', 'age_category.id', 'tournament_age_category.age_category')->where('tournament_id', $tid)->get();
        }
    }else{
        $cat = TourAgeCategory::join('age_category', 'age_category.id', 'tournament_age_category.age_category')->where('tournament_id', $tid)->get();
    }
        $players = TournamentsTeam::join('users_details', 'users_details.user_id', '=', 'tournament_teams.user_id')
        ->join('users', 'users.id', '=', 'users_details.user_id')
        ->where('tournament_id', $tid)->where('state_id', $state_id)
        ->select('users.username','users.name','users_details.photo','users_details.user_id','users_details.gender','users_details.email','users_details.mobile','users_details.dob','tournament_teams.id','tournament_teams.jersey_no')->get();

        $coach = TourCoaches::where('tournament_id',$tid)->where('state_id', $state_id)->get();
        return view('admin.tournaments.entryview',compact('tournament','tid','players','coach','cat','state_id'));
    }
    public function getAllAgeCategory()
    {
        Log::info("Get all age category");

        $ageCategoryArr = MAgeCategory::all();
        Log::info($ageCategoryArr);

      return response()->json(['agecategoryarr' => $ageCategoryArr]);
    }
    public function store(Request $request)
    {
        try {
            \DB::beginTransaction();
        // Validate the form data
        $validatedData = $request->validate([
            'tournamentname' => 'required|string|max:255',
            'tournamentlocation' => 'required|string|max:255',
            'venueaddress' => 'required|string',
            'pincode' => 'required|digits:6',
            'state' => 'required',
            'entrylastdate' => 'required|date',
            'entrylastdatewithfine' => 'required|date',
            'tournamentstartdate' => 'required|date',
            'tournamentenddate' => 'required|date',
            'entriestype' => 'required',

            // Add more validation rules as needed
        ]);
            \Log::info($request->all());
        // Create a new Tournament instance
        $tournament = new Tournaments();

        // Assign validated data to the model attributes
        $tournament->tournament_name = $validatedData['tournamentname'];
        $tournament->tournament_location = $validatedData['tournamentlocation'];
        $tournament->venue_address = $validatedData['venueaddress'];
        $tournament->pincode = $validatedData['pincode'];
        $tournament->state_id = $validatedData['state'];
        $tournament->entry_last_date = $validatedData['entrylastdate'];
        $tournament->entry_last_date_with_fine = $validatedData['entrylastdatewithfine'];
        $tournament->tournament_start_date = $validatedData['tournamentstartdate'];
        $tournament->tournament_end_date = $validatedData['tournamentenddate'];
        $tournament->entries_type = $validatedData['entriestype'];

        $folder = 'BFI/circular';
        $logofolder = 'BFI/logo';
        // Handle the circular file upload
        if ($request->hasFile('circularfile')) {
            $file = $request->file('circularfile');
            $filename = time()."_".$file->getClientOriginalName(); // You might want to sanitize this
            $s3Path = $folder . '/' . $filename;

            if (\Storage::disk('s3')->put($s3Path, file_get_contents($file))) {
                $tournament->circular_file = $filename;
            } else {
                // Handle the case where the file upload fails
                return redirect()->back()->with('error', 'Failed to upload the circular file.');
            }
        }
        if($request->hasFile('logo')){
            $files = $request->file('logo');
            $filenames = $files->getClientOriginalName(); // You might want to sanitize this
            $s3Paths = $logofolder . '/' . $filenames;

            if (\Storage::disk('s3')->put($s3Paths, file_get_contents($files))) {
                $tournament->logo = $filenames;
            } else {
                // Handle the case where the file upload fails
                return redirect()->back()->with('error', 'Failed to upload the logo.');
            }
        }

        // Save the tournament details
        $tournament->save();

        $ageCategorySelectionCount = request('hidden_age_category_count');
        for ($i = 1; $i <= $ageCategorySelectionCount; $i++) {
            $ageCategory = new TourAgeCategory();
            $ageCategory->tournament_id = $tournament->id;
            $ageCategory->age_category = request('agecategory_' . $i);
            $ageCategory->dob_start_date = request('dob_start_date_' . $i);
            $ageCategory->dob_end_date = request('dob_end_date_' . $i);
            $ageCategory->players_count = request('player_count_' . $i);
            //$ageCategory->officials_count = request('offcial_count_' . $i);
            $ageCategory->save();
        }
        \DB::commit();
        // Redirect or respond as needed
        return redirect()->route('admin/addtournament')->with('success', 'Tournament created successfully');
    } catch (\Exception $e) {
        \DB::rollBack();
        return redirect()->back()->with('error', 'An error occurred. Please try again.');
    }
    }
    public function tournamentList(){

        $tournaments = Tournaments::all();
        return view('admin.tournaments.tournamentlist',compact('tournaments'));
    }
    public function tournamentDetails($id){

        $tournament = Tournaments::join('mstate', function ($join) {
            $join->on('mstate.id', '=', DB::raw('CAST(tournaments.state_id AS BIGINT)'));
        })
        ->where('tournaments.id', $id)
        ->select('tournaments.*', 'mstate.stateName as stateName')
        ->first();
        Log::Info($tournament);
        return view('admin.tournaments.tournamentdetails',compact('tournament'));
    }
    public function editTournament($id){
        $tournament = Tournaments::join('mstate', function ($join) {
            $join->on('mstate.id', '=', DB::raw('CAST(tournaments.state_id AS BIGINT)'));
        })
        ->where('tournaments.id', $id)
        ->select('tournaments.*', 'mstate.stateName as stateName')
        ->first();
        $states = MState::all();
        return view('admin.tournaments.edittournament',compact('tournament','states'));
    }
    public function updateTournament(Request $request){
        try {
            \DB::beginTransaction();
        // Validate the form data
        $validatedData = $request->validate([
            'tournamentname' => 'required|string|max:255',
            'tournamentlocation' => 'required|string|max:255',
            'venueaddress' => 'required|string',
            'pincode' => 'required|digits:6',
            'state' => 'required',
            'entrylastdate' => 'required|date',
            'entrylastdatewithfine' => 'required|date',
            'tournamentstartdate' => 'required|date',
            'tournamentenddate' => 'required|date',
            'entriestype' => 'required|in:1,2,3',
            'circularfile' => 'required|mimes:pdf|max:5120', // Ensure it's a PDF and less than 5MB
            // Add more validation rules as needed
        ]);
            \Log::info($request->all());
        // Create a new Tournament instance
        $tournament = Tournaments::find($request->tourid);

        // Assign validated data to the model attributes
        $tournament->tournament_name = $validatedData['tournamentname'];
        $tournament->tournament_location = $validatedData['tournamentlocation'];
        $tournament->venue_address = $validatedData['venueaddress'];
        $tournament->pincode = $validatedData['pincode'];
        $tournament->state_id = $validatedData['state'];
        $tournament->entry_last_date = $validatedData['entrylastdate'];
        $tournament->entry_last_date_with_fine = $validatedData['entrylastdatewithfine'];
        $tournament->tournament_start_date = $validatedData['tournamentstartdate'];
        $tournament->tournament_end_date = $validatedData['tournamentenddate'];
        $tournament->entries_type = $validatedData['entriestype'];
        $folder = 'BFI/circular';
        // Handle the circular file upload
        if ($request->hasFile('circularfile')) {
            $file = $request->file('circularfile');
            $filename = $file->getClientOriginalName(); // You might want to sanitize this
            $s3Path = $folder . '/' . $filename;

            if (\Storage::disk('s3')->put($s3Path, file_get_contents($file))) {
                $tournament->circular_file = $filename;
            } else {
                // Handle the case where the file upload fails
                return redirect()->back()->with('error', 'Failed to upload the circular file.');
            }
        }
        // Save the tournament details
        $tournament->save();

        // $ageCategorySelectionCount = request('hidden_age_category_count');
        // for ($i = 1; $i <= $ageCategorySelectionCount; $i++) {
        //     $ageCategory = new TourAgeCategory();
        //     $ageCategory->tournament_id = $tournament->id;
        //     $ageCategory->age_category = request('agecategory_' . $i);
        //     $ageCategory->dob_start_date = request('dob_start_date_' . $i);
        //     $ageCategory->dob_end_date = request('dob_end_date_' . $i);
        //     $ageCategory->save();
        // }
        \DB::commit();
        // Redirect or respond as needed
        return redirect()->route('admin/addtournament')->with('success', 'Tournament updated successfully');
    } catch (\Exception $e) {
        \DB::rollBack();
        return redirect()->back()->with('error', 'An error occurred. Please try again.');
    }
    }
    public function createTeam($id){
        $tournament = Tournaments::find($id);
        return view('admin.tournaments.createteam' ,compact('tournament'));
    }
}
