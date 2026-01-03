<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabelOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'locale',
        'value',
        'group',
        'description',
    ];
}
