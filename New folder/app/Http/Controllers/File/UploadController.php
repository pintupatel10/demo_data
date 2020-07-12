<?php

namespace App\Http\Controllers\File;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use MediaUploader;

class UploadController extends Controller
{

    public function __construct()
    {
        //parent::__construct();
    }

    public function upload(Request $request)
    {
        $params = [];
        $tag    = $request->get("tag",false);
        $return = [];
        // upload medias
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $media    = MediaUploader::fromSource($file)->toDirectory($tag)->useHashForFilename()->upload();
            $return = [
                "id"  => $media->id,
                "url" => $media->getUrl(),
            ];
        }

        return response()->json($return);
    }

}
