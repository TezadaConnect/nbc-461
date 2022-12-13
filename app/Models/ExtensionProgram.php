<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtensionProgram extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = [];

    public function extensionprogramdocument() {
        return $this->hasMany(\App\Models\ExtensionProgramDocument::class);
    }

    public function user() {
        return $this->belongsTo(\App\Models\User::class);
    }
}
