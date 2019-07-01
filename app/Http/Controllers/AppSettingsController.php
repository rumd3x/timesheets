<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\AppSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
                'display' => AppSetting::where('name', AppSetting::SPREADSHEET_CURRENT_TEMPLATE_FILENAME)->first(),
                'description' => 'Upload Spreasheet Template (csv, xls)',
                'type' => 'file',
                'name' => AppSetting::SPREADSHEET_CURRENT_TEMPLATE_FILENAME,
            ],
            [
                'display' => 'Cell with the Person Name',
                'type' => 'text',
                'name' => AppSetting::SPREADSHEET_HEADER_PERSON_NAME,
                'value' => AppSetting::where('name', AppSetting::SPREADSHEET_HEADER_PERSON_NAME)->first(),
            ],
            [
                'display' => 'Cell with header for respective month (Column + Row)',
                'type' => 'text',
                'name' => AppSetting::SPREADSHEET_HEADER_MONTH_CELL,
                'value' => AppSetting::where('name', AppSetting::SPREADSHEET_HEADER_MONTH_CELL)->first(),
            ],
            [
                'display' => 'The format of the outputted date string on the header cell',
                'type' => 'text',
                'name' => AppSetting::SPREADSHEET_HEADER_MONTH_FORMAT,
                'value' => AppSetting::where('name', AppSetting::SPREADSHEET_HEADER_MONTH_FORMAT)->first(),
            ],
            [
                'display' => 'Initial Column',
                'type' => 'text',
                'name' => AppSetting::SPREADSHEET_INITIAL_COLUMN,
                'value' => AppSetting::where('name', AppSetting::SPREADSHEET_INITIAL_COLUMN)->first(),
            ],
            [
                'display' => 'Initial Row',
                'type' => 'text',
                'name' => AppSetting::SPREADSHEET_INITIAL_ROW,
                'value' => AppSetting::where('name', AppSetting::SPREADSHEET_INITIAL_ROW)->first(),
            ],
            [
                'display' => 'Spreadsheet Recipients (Emails)',
                'type' => 'text',
                'name' => AppSetting::SPREADSHEET_GENERATION_EMAILS_REAL_RECIPIENTS,
                'value' => AppSetting::where('name', AppSetting::SPREADSHEET_GENERATION_EMAILS_REAL_RECIPIENTS)->first(),
            ],
            [
                'display' => 'Target Hours for Spreadsheet Generation',
                'type' => 'text',
                'name' => AppSetting::SPREADSHEET_GENERATION_TARGET_HOURS,
                'value' => AppSetting::where('name', AppSetting::SPREADSHEET_GENERATION_TARGET_HOURS)->first(),
            ],
            [
                'display' => 'Target Hours Spreadsheet Recipients (Emails)',
                'type' => 'text',
                'name' => AppSetting::SPREADSHEET_GENERATION_EMAILS_TARGET_RECIPIENTS,
                'value' => AppSetting::where('name', AppSetting::SPREADSHEET_GENERATION_EMAILS_TARGET_RECIPIENTS)->first(),
            ],
        ];
        return view('settings', compact('inputs'));
    }

    public function save(Request $request)
    {
        $request->validate([
            AppSetting::SPREADSHEET_CURRENT_TEMPLATE_FILENAME => 'nullable|file|mimes:csv,xls,xlsx',
            AppSetting::SPREADSHEET_INITIAL_ROW => 'required|min:1|max:99999|integer',
            AppSetting::SPREADSHEET_INITIAL_COLUMN => 'required|min:1|max:2|alpha',
            AppSetting::SPREADSHEET_HEADER_MONTH_CELL => 'required|min:2|max:3|alpha_num',
            AppSetting::SPREADSHEET_GENERATION_EMAILS_REAL_RECIPIENTS => 'present|email_list',
            AppSetting::SPREADSHEET_GENERATION_EMAILS_TARGET_RECIPIENTS => 'present|email_list',
            AppSetting::SPREADSHEET_GENERATION_TARGET_HOURS => 'present|nullable|integer',
            AppSetting::SPREADSHEET_HEADER_MONTH_FORMAT => 'present|nullable',
            AppSetting::SPREADSHEET_HEADER_PERSON_NAME => 'required|min:2|max:3|alpha_num',
        ]);

        if ($request->hasFile(AppSetting::SPREADSHEET_CURRENT_TEMPLATE_FILENAME)) {
            $file = $request->file(AppSetting::SPREADSHEET_CURRENT_TEMPLATE_FILENAME);
            if ($file->getError() !== UPLOAD_ERR_OK) {
                return Redirect::back()->withErrors([$file->getErrorMessage()]);
            }

            $fileSetting = AppSetting::where('name', AppSetting::SPREADSHEET_CURRENT_TEMPLATE_FILENAME)->first();
            if (!$fileSetting) {
                $fileSetting = new AppSetting();
                $fileSetting->name = AppSetting::SPREADSHEET_CURRENT_TEMPLATE_FILENAME;
            }
            if ($fileSetting->value && Storage::disk('local')->exists('template.old')) {
                Storage::disk('local')->delete('template.old');
            }
            if ($fileSetting->value && Storage::disk('local')->exists($fileSetting->value)) {
                Storage::disk('local')->move($fileSetting->value, 'template.old');
            }
            $fileSetting->value = $file->getClientOriginalName();
            $fileSetting->save();
            $file->storeAs('/', $file->getClientOriginalName());
        }

        foreach ($request->only([
            AppSetting::SPREADSHEET_INITIAL_ROW,
            AppSetting::SPREADSHEET_INITIAL_COLUMN,
            AppSetting::SPREADSHEET_GENERATION_EMAILS_REAL_RECIPIENTS,
            AppSetting::SPREADSHEET_GENERATION_EMAILS_TARGET_RECIPIENTS,
            AppSetting::SPREADSHEET_GENERATION_TARGET_HOURS,
            AppSetting::SPREADSHEET_HEADER_MONTH_CELL,
            AppSetting::SPREADSHEET_HEADER_MONTH_FORMAT,
            AppSetting::SPREADSHEET_HEADER_PERSON_NAME,
        ]) as $name => $value) {
            $appSetting = AppSetting::where('name', $name)->first();
            if (!$appSetting) {
                $appSetting = new AppSetting();
                $appSetting->name = $name;
            }
            if ($appSetting->value != $value) {
                $appSetting->value = $value;
                $appSetting->save();
            }
        }

        return Redirect::back();
    }
}
