<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Library\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{

    public function __construct()
    {
        //parent::__construct();
    }

    public function index(Request $request, $id)
    {
        return view("patient.book");
    }

}
