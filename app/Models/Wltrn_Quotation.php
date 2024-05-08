<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

date_default_timezone_set("Asia/Kolkata");
class Wltrn_Quotation extends Model
{
    protected $table = 'wltrn_quotation';
    use HasFactory;

    protected $fillable = [
        'isfinal'
    ];

    
    // use HasFactory;
}
