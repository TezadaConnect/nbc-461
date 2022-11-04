<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use App\Models\Maintenance\Quarter;
use App\Http\Controllers\Controller;

class SpatieController extends Controller
{
    public function index () {
        return view('spatie.index');
    }
}
