<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $inputs = [
            [
                'display' => 'Template Spreadsheet',
                'type' => 'file',
                'name' => 'template',
            ],
            [
                'display' => 'Initial Column',
                'type' => 'text',
                'name' => 'column',
            ],
            [
                'display' => 'Initial Row',
                'type' => 'text',
                'name' => 'row',
            ],
        ];
        return view('settings', compact('inputs'));
    }
}
