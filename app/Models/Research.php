<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Research extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = [];

    public function department() {
        return $this->belongsTo(\App\Models\Maintenance\Department::class);
    }

    public function document() {
        return $this->belongsTo(\App\Models\Document::class);
    }
}
