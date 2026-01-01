<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploaded_by_user_id',
        'label',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'kind',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
