<?php

namespace App\Http\Controllers;

use App\Client;
use App\Events\UserLogCreated;
use App\Http\Requests\ModeratorCreateRequest;
use App\Initial;
use App\Moderator;
use App\Payment;
use App\Program;
use App\School;
use App\Services\ModeratorService;
use App\User;
use App\UserProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ModeratorController extends Controller
{
    private $moderatorService;

    public function __construct(ModeratorService $moderatorService)
    {
        $this->moderatorService = $moderatorService;    
    }

    public function showDashboard()
    {
        return Inertia::render('Moderator/Dashboard', [
            'programs'      =>  Program::orderBy('name', 'asc')->get(),
            'clients'       =>  UserProgram::orderBy('id', 'asc')->with('program')->get(),
            'schools'        =>  School::orderBy('name', 'desc')
                ->with(['clients' => function($query) {
                    return $query->with('userProgram');
                }])
                ->get()
        ]);
    }

    public function showModeratorEntry()
    {
        return Inertia::render('Superadmin/ModeratorEntry', [
            'moderators'    =>  Moderator::orderBy('first_name', 'asc')->with('user')->get()
        ]);
    }

    public function getAllModerators()
    {
        return Moderator::orderBy('first_name', 'asc')->with('user')->get();
    }

    public function createModerator()
    {
        return Inertia::render('Superadmin/Moderator/Create');
    }

    public function storeModerator(ModeratorCreateRequest $request)
    {
        $user = $this->moderatorService->registerNewModerator($request);

        event(new UserLogCreated([
            'user_id'   =>  $request->user()->id,
            'action'    =>  $user->email . ' has been registered.'
        ]));
        
        return redirect()->back();
    }

    public function editModerator($userId)
    {
        return Inertia::render('Superadmin/Moderator/Edit', [
            'moderators'    =>  Moderator::where('user_id', $userId)->first()
        ]);
    }

    public function updateModerators(Request $request)
    {
        $request->validate([
            'first_name'    =>  'required',
            'middle_name'   =>  'required',
            'last_name'     =>  'required',
        ]);

        Moderator::where('user_id', $request->user_id)->update([
            'first_name'    =>  $request->first_name,
            'middle_name'   =>  $request->middle_name,
            'last_name'     =>  $request->last_name
        ]);

        event(new UserLogCreated([
            'user_id'   =>  $request->user()->id,
            'action'    =>  User::find($request->user_id)->email . ' details has been updated.'
        ]));

        return redirect()->back();
    }

    public function deleteModerator(Request $request, $userId)
    {
        $user = User::find($userId);

        Moderator::where('user_id', $userId)->delete();

        event(new UserLogCreated([
            'user_id'   =>  $request->user()->id,
            'action'    =>  'Account has been deleted.'
        ]));
        
        $user->delete();

        return redirect()->back();
    }

    public function showClients()
    {
        return Inertia::render('Moderator/Clients', [
            'clients'   =>  Client::orderBy('created_at', 'desc')
                ->with('user')
                ->with('school')
                ->with(['userProgram' => function($query) {
                    return $query->with('program');
                }])
                ->paginate(15)
        ]);
    }

    public function showSelectedClient($id)
    {
        $client = Client::where('user_id', $id)->with('user')->with('school')->first();
        return Inertia::render('Moderator/Client/SelectedClient', [
            'client'    =>  $client,
            'payments'  =>  Payment::where('user_id', $id)->get()->map(function($payment) {
                return [
                    'id'        =>  $payment->id,
                    'user_id'   =>  $payment->user_id,
                    'purpose'   =>  $payment->purpose,
                    'isVerified'=>  $payment->isVerified,
                    'path'      =>  (Auth::check()) ? '/slips/' . $payment->path : 'Auth required.',
                    'created_at'=>  $payment->created_at->toDayDateTimeString()
                ];
            }),
            'userPrograms' =>  UserProgram::where('user_id', $client->user_id)
                ->with('program')
                ->with('course')
                ->get()
        ]);
    }

    public function showSelectedProgram($id, $programId)
    {
        return Inertia::render('Moderator/Client/SelectedProgram', [
            'userProgram'=>  UserProgram::where('user_id', $id)
                ->where('program_id', $programId)
                ->first()
            ,
            'initials'   =>  Initial::where('program_id', $programId)
                ->with('clientInitial')
                ->get()
                ->map(function ($initials) use ($id) {
                    return [
                        'id'                =>  $initials->id,
                        'name'              =>  $initials->name,
                        'client_initial'    =>  $initials->clientInitial->where('initial_id', $initials->id)->where('user_id', $id)->first()
                    ];
            }),
        ]);
    }

    public function searchStudentByLastName(Request $request)
    {
        return Client::where('last_name', $request->last_name)
            ->with('user')
            ->with(['userProgram' => function($query) {
                return $query->with('program');
            }])
            ->paginate(15);
    }
}
