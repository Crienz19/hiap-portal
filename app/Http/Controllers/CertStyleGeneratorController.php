<?php

namespace App\Http\Controllers;

use App\CertStyle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertStyleGeneratorController extends Controller
{
    public function addStyleToCert(Request $request)
    {
        CertStyle::create([
            'cert_id'   =>  $request->cert_id,
            'name'      =>  '',
            'class_name'=>  '',
            'style'     =>  '',
        ]);

        return redirect()->back();
    }

    public function updateStyleToCert(Request $request, $styleId)
    {
        $stringedStyle = [
            'position:absolute',
            'font-family:'.$request->style['font_style'],
            'font-weight:'.$request->style['font_weight'],
            'font-size:'.$request->style['font_size'],
            'text-align:'.$request->style['text_align'],
            'color:'.$request->style['color'],
            'top:'.$request->style['top_pos'],
            'bottom:'.$request->style['bot_pos'],
            'right:'.$request->style['right_pos'],
            'left:'.$request->style['left_pos']
        ];

        CertStyle::where('id', $styleId)
            ->update([
                'name'          =>  $request->name,
                'class_name'    =>  '.'.Str::snake($request->name),
                'style'         =>  json_encode($stringedStyle),
                'raw_style'     =>  json_encode($request->style)
            ]);

        return redirect()->back();
    }
}
