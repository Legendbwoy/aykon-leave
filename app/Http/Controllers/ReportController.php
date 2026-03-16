<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the reports.
     */
    public function index()
    {
        // You can add logic to fetch and display reports here
        return view('reports.index');
    }

    // You may want to add attendance() and export() methods as well if needed by your routes
}
