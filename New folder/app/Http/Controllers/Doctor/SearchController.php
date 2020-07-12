<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Library\Api;
use Illuminate\Http\Request;

class SearchController extends Controller
{

    public function __construct()
    {
        //parent::__construct();
    }

    public function index(Request $request, $location = "", $q = "")
    {
        $params = $request->all();

        if (!empty($location)) {
            $params["location"] = $location;
        }

        if (!empty($q)) {
            $params["q"] = $q;
        }

        $result = \Api::sendRequest("GET", "/doctor/search", $params);

        if (!empty($result["data"]) && $result["code"] == 200) {
            $data = $result["data"];
            return view("doctor.search", compact('data'));
        }

    }
}
