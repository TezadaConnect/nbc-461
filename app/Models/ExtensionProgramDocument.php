<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtensionProgramDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function extensionprogram() {
        return $this->belongsTo(\App\Models\ExtensionProgram::class);
    }
}
