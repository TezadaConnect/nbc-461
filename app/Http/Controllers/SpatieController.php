<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BackupDatabase;
use Illuminate\Http\Request;

class SpatieController extends Controller
{
    public function index() {
        return view('spatie.index', ['databaseBackup' => BackupDatabase::all()]);
    }

    /**
     * This method saves the selected frequenct into the table
     */
    public function saveFrequency(Request $frequency) {
        $this->deleteExistingFrequency();
        $newFrequency = new BackupDatabase;
        $newFrequency->frequency = $frequency->freq;
        $newFrequency->save();
        \Log::info(json_encode($frequency->all()));
        return redirect()->route('spatie')->with('success', 'Download frequency has been saved.');
    }

    /**
     * Since we only need 1 record for the frequency of download,
     * we should delete all existing record
     * before inserting into the BackupDatabase table
     * This method deletes all existing records in BackupDatabase table
     */
    private function deleteExistingFrequency() {
        BackupDatabase::truncate();
    }

    /**
     * This method runs the spatie backup command
     */
    public function downloadBackup() {
        \Artisan::call('config:cache');
        \Artisan::call('backup:run --only-db');
        // $path = storage_path('app/backups/*');
        // $latest_ctime = 0;
        // $latest_filename = '';
        // $files = glob($path);
        // foreach($files as $file)
        // {
        //         if (is_file($file) && filectime($file) > $latest_ctime)
        //         {
        //                 $latest_ctime = filectime($file);
        //                 $latest_filename = $file;
        //         }
        // }
        //return redirect()->route('spatie');
    }
}