<?php

namespace App\Http\Controllers;

use App\Client;
use App\ClientInitial;
use App\Course;
use App\Grade;
use App\Log;
use App\Mail\NewApplicantNotification;
use App\OnlineProgram;
use App\Payment;
use App\Program;
use App\School;
use App\User;
use App\UserProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Services\ClientApplicationService;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    private $clientApplicationService;

    public function __construct(ClientApplicationService $clientApplicationService)
    {
        $this->clientApplicationService = $clientApplicationService;
    }

    public function showApplicationForm(Request $request)
    {
        return Inertia::render('Client/ApplicationForm', [
            'auth'      =>  $request->user(),
            'program_id'=>  $request->user()->program_id,
            'schools'   =>  School::orderBy('name')->get(),
            'courses'   =>  Course::orderBy('id')->get(),
            'programs'  =>  Program::orderBy('name')->where('isActive', 1)->get()
        ]);
    }

    public function showDashboard(Request $request)
    {
        if ($request->user()->isFilled == true) {
            return Inertia::render('Client/Dashboard', [
                'client'             =>  Client::where('user_id', $request->user()->id)
                    ->with('user')
                    ->with('school')
                    ->first(),
                'schools'           =>  School::orderBy('name', 'desc')->get(),
                'onlinePrograms'    =>  OnlineProgram::orderBy('name', 'desc')->get(),
                'payments'          =>  Payment::where('user_id', $request->user()->id)
                    ->with(['track'])
                    ->get()
                    ->map(function($payment) {
                    return [
                        'id'            =>  $payment->id,
                        'purpose'       =>  $payment->purpose,
                        'mop'           =>  $payment->mop,
                        'amount_paid'   =>  $payment->amount_paid,
                        'isVerified'    =>  $payment->isVerified,
                        'track'         =>  [
                            'id'    =>  $payment->track->id,
                            'name'  =>  $payment->track->program->name
                        ],
                        'date_paid'     =>  $payment->date_paid,
                        'created_at'    =>  $payment->created_at->toDayDateTimeString()
                    ];
                }),
                'userPrograms'      =>  UserProgram::where('user_id', $request->user()->id)
                    ->with('program')
                    ->with('course')
                    ->get()
            ]);
        } else {
            return redirect()->route('cl.application');
        }
    }

    public function showClientRequirements($programId)
    {
        return Inertia::render('Client/UserRequirements', [
            'programId' =>  $programId
        ]);
    }

    public function sendApplication(Request $request)
    {
        $request->validate([
            'first_name'    =>  'required',
            'last_name'     =>  'required',
            'address'       =>  'required',
            'contact_number'=>  'bail|required|numeric',
            'school'        =>  'required',
        ]);  

        if($this->clientApplicationService->createClientApplication($request)) {
            User::find($request->user()->id)->update([
                'isFilled'      =>  true
            ]);
        }
        
        return redirect()->route('cl.dashboard');
    }

    public function getLoggedClientDetails(Request $request)
    {
        return Client::where('user_id', $request->user()->id)
            ->with('user')
            ->with('course')
            ->first();
    }

    public function getAllClientDetails()
    {
        return $this->clientApplicationService->getClientRecords();
    }

    public function setApplicationStatus(Request $request, $id)
    {
        $this->clientApplicationService->updateApplicationStatus($request->application_status, $id);

        return redirect()->back()->with([
            'message'   =>  'Application Status Updated.'
        ]);
    }

    public function deleteClientDetails(Request $request, $userId)
    {

        $user = User::where('id', $userId);

        Log::create([
            'user_id'   =>  $request->user()->id,
            'action'    =>  $user->first()->email . ' has been deleted.'
        ]);

        $user->delete();
        Client::where('user_id', $userId)->delete();
        ClientInitial::where('user_id', $userId)->delete();
        Grade::where('user_id', $userId)->delete();
        Log::where('user_id', $userId)->delete();

        return redirect()
            ->back()
            ->with('message', 'Client deleted.');
    }

    public function manualVerification($userId)
    {
        User::where('id', $userId)->update([
            'email_verified_at' =>  now()
        ]);

        return redirect()->back();
    }

    public function setToFilled($userId)
    {
        User::where('id', $userId)->update([
            'isFilled' =>  true
        ]);

        return redirect()->back();
    }

    public function setToUnfilled($userId)
    {
        User::where('id', $userId)->update([
           'isFilled'   =>  false
        ]);

        return redirect()->back();
    }

    public function updateClientDetails(Request $request)
    {
        $this->clientApplicationService->updateDetails($request);

        return redirect()
            ->back()
            ->with('message', 'Client details updated.');
    }

    public function filterClients(Request $request)
    {
        return UserProgram::orderBy('created_at', 'desc')
                ->with(['client' => function($query) use ($request) {
                    return $query->with('user')
                                    ->with('school')
                                    ->with('onlineProgram')
                                    ->whereBetween('created_at', [date($request->from), date($request->to)])   
                                    ->where('school_id', 'like', '%' .$request->school_id . '%')
                                    ->get();
                }])
                ->with('program')
                ->whereBetween('created_at', [date($request->from), date($request->to)])
                ->get();
    }

    public function setUserPasswordToDefault(Request $request)
    {
        User::where('id', $request->user_id)->update([
            'password'  =>  Hash::make('p@ssw0rd')
        ]);

        return redirect()->back();
    }
}
