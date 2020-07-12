<?php

namespace App\Http\Controllers;

class SiteController extends Controller
{
    public function index()
    {
        $result = \Api::sendRequest("GET", "/patient/home");
        if (!empty($result["data"]) && $result["code"] == 200) {
            $data = $result["data"];
            return view('welcome', compact('data'));
        }
    }
}
