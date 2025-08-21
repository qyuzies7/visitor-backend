<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_card_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type'
    ];

    public function visitorCard(){
        return $this->belongsTo(VisitorCard::class);
    }
}
