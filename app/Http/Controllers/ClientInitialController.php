<?php

namespace App\Http\Controllers;

use App\ClientInitial;
use App\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ClientInitialController extends Controller
{
    public function getClientInitialRequirements (Request $request)
    {
        return ClientInitial::where('user_id', $request->user()->id)
            ->with('initial')
            ->get();
    }

    public function storeClientInitialRequirement(Request $request)
    {
        $request->validate([
            'file'  =>  'required'
        ]);

        $filename = time() . '.' . $request->file->extension();
        $request->file->move(public_path('uploads'), $filename);

        Log::create([
            'user_id'   =>  $request->user()->id,
            'action'    =>  $filename . ' has been uploaded.'
        ]);

        return ClientInitial::create([
            'user_id'       =>  $request->user()->id,
            'initial_id'    =>  $request->input('initial_id'),
            'status'        =>  true,
            'file_path'     =>  $filename
        ]);
    }

    public function deleteClientInitialRequirement($id)
    {
        $initial = ClientInitial::where('id', $id);

        if (File::exists('uploads/' . $initial->first()->file_path)) {
            File::delete('uploads/' . $initial->first()->file_path);
        }

        Log::create([
            'user_id'   =>  $initial->first()->user_id,
            'action'    =>  $initial->first()->file_path . ' has been deleted.'
        ]);

        $initial->delete();


    }
}
