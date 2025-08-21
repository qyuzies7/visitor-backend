<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitorCardStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_card_id',
        'performed_by_user_id',
        'performed_by_name_cached',
        'old_status',
        'new_status',
        'notes',
        'changed_at',
    ];

    public function visitorCard(){
        return $this->belongsTo(VisitorCard::class);
    }

    public function performedBy(){
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
