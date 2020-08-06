<?php

namespace App\Http\Controllers;

use App\Client;
use App\Grade;
use App\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GradeController extends Controller
{
    public function storeGrade(Request $request)
    {
        $request->validate([
            'grade'     =>  'required'
        ]);

        Grade::create([
            'program_id'=>  $request->program_id,
            'lesson_id' =>  $request->lesson_id,
            'user_id'   =>  $request->user_id,
            'title'     =>  $request->title,
            'grade'     =>  $request->grade
        ]);

        return redirect()->back();
    }

    public function getClientGrades()
    {
        $user = Client::where('user_id', Auth::user()->id)->first();
        // return Grade::where('user_id', Auth::user()->id)->get();
        return Lesson::where('program_id', $user->program_id)->with(['grade' => function ($query) use ($user) {
            $query->where('user_id', $user->user_id);
        }])->get();
    }

    public function deleteGrade($gradeId)
    {
        Grade::where('id', $gradeId)->delete();

        return redirect()->back();
    }
}
