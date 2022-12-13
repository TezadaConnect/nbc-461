<?php

namespace App\Services;

use Illuminate\Http\Request;

class DateContentService {
    public function checkDateContent(Request $request, $field_name) {
        if (empty($request->input($field_name))) {
            $field_name = null;
        }
        else {
            $field_name = date("Y-m-d", strtotime($request->input($field_name)));
        }

        return $field_name;
    }

    
    /**
     * =============================================================================================
     * 
     * CREATED BY: KENYLEEN D. PAN
     * Checks if the date has valid format specified in the blade forms.
     * @param String $date.
     * @return Bool.
     * 
     * =============================================================================================
     */
    public function isValidDate($date) {
        return date('m/d/Y', strtotime($date)) === $date;
    }
}