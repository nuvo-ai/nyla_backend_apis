<?php

namespace App\Models\General;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes , SoftDeletes;
    public $table = "currencies";

    protected $fillable = [
        "name",
        "type",
        "short_name",
        "logo",
        "status",
    ];

}