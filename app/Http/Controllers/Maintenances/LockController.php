<?php

namespace App\Http\Controllers\Maintenances;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Report,
    Maintenance\Quarter,
};

class LockController extends Controller
{
    public static function isLocked($referenceID, $categoryID){
        $currentQuarterYear = Quarter::find(1);

        if(Report::where('report_reference_id', $referenceID)
            ->where('report_category_id', $categoryID)
            ->where('user_id', auth()->id())
            ->where('report_year', $currentQuarterYear->current_year)
            ->where('report_quarter', $currentQuarterYear->current_quarter)->exists()){
            $data = Report::where('report_reference_id', $referenceID)
                        ->where('report_category_id', $categoryID)
                        ->where('user_id', auth()->id())
                        ->where('report_year', $currentQuarterYear->current_year)
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->first();
            
            $flag = 1;
            
            if($data->report_category_id >= 1 && $data->report_category_id <= 8)
                if($data->researcher_approval == "0")
                    $flag = 0;
            
            if($data->report_category_id >= 9 && $data->report_category_id <= 14)
                if($data->extensionist_approval == "0")
                    $flag = 0;

            if($data->chairperson_approval == "0")
                $flag = 0;
            
                
            if($data->dean_approval == "0")
                $flag = 0;
                
                
            if($data->sector_approval == "0")
                $flag = 0;
                
                
            if($data->ipqmso_approval == "0")
                $flag = 0;
            
            if($flag == 1)
                return true;
            
        }

        return false;
    }

    public static function isNotLocked($referenceID, $categoryID){
        $currentQuarterYear = Quarter::find(1);

        if(Report::where('report_reference_id', $referenceID)
            ->where('report_category_id', $categoryID)
            ->where('user_id', auth()->id())
            ->where('report_year', $currentQuarterYear->current_year)
            ->where('report_quarter', $currentQuarterYear->current_quarter)->exists()){
            $data = Report::where('report_reference_id', $referenceID)
                        ->where('report_category_id', $categoryID)
                        ->where('user_id', auth()->id())
                        ->where('report_year', $currentQuarterYear->current_year)
                        ->where('report_quarter', $currentQuarterYear->current_quarter)
                        ->first();
            
            $flag = 1;
            
            if($data->report_category_id >= 1 && $data->report_category_id <= 8)
                if($data->researcher_approval == "0")
                    $flag = 0;
            
            if($data->report_category_id >= 9 && $data->report_category_id <= 14)
                if($data->extensionist_approval == "0")
                    $flag = 0;

            if($data->chairperson_approval == "0")
                $flag = 0;
            
                
            if($data->dean_approval == "0")
                $flag = 0;
                
                
            if($data->sector_approval == "0")
                $flag = 0;
                
                
            if($data->ipqmso_approval == "0")
                $flag = 0;
            
            if($flag == 1)
                return false;
            
        }

        return true;
    }

    //This function is useful in research controller that checks whether the research is submitted once or not (because research must be submitted only once.)
    public static function isReportSubmitted($referenceID, $categoryID){
        if(Report::where('report_reference_id', $referenceID)
            ->where('report_category_id', $categoryID)
            ->where('user_id', auth()->id())
            ->exists()){
                return true;
        } return false;
    }
}
