<?php

namespace App\Http\Controllers;

use App\Client;
use App\Initial;
use App\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InitialController extends Controller
{
    public function showInitialRequirements($programId)
    {
        return Inertia::render('Superadmin/ProgramInitial', [
            'program' => Program::find($programId)
        ]);
    }

    public function storeInitialRequirement(Request $request, $programId)
    {
        $request->validate([
            'name'          =>  'required',
            'description'   =>  'required',
        ]);

        if ($request->has('file')) {
            $filename = Str::snake($request->name) . '-' . time() . '.' . $request->file->extension();
            $request->file->move(public_path('initials'), $filename);
        }

        return Initial::create([
            'program_id'    =>  $programId,
            'name'          =>  $request->name,
            'description'   =>  $request->description,
            'file_path'     =>  $request->has('file') ? $filename : ''
        ]);
    }

    public function deleteInitialRequirement($initialId)
    {
        $initial = Initial::where('id', $initialId);

        if(File::exists('initials/' . $initial->first()->file_path)) {
            File::delete('initials/' . $initial->first()->file_path);
        }

        $initial->delete();
    }

    public function getAllInitialRequirements($programId)
    {
        return Initial::where('program_id', $programId)->get();
    }

    public function getInitialRequirementsForClient(Request $request)
    {
        return Initial::where('program_id', Client::where('user_id', $request->user()->id)->first()->program_id)
            ->with('clientInitial')
            ->get()
            ->map(function ($initials) use ($request) {
                return [
                    'id'    =>  $initials->id,
                    'name'  =>  $initials->name,
                    'file_path' =>  $initials->file_path,
                    'client_initial'    =>  $initials->clientInitial->where('initial_id', $initials->id)->where('user_id', $request->user()->id)->first()
                ];
            });
    }
}
