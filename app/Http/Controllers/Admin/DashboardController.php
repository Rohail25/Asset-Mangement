<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $client = Client::orderBy('name')->get();
        return view('admin.dashboard', compact('client'));
    }
}
