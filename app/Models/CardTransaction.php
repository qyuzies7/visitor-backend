<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CardTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_card_id',
        'transaction_type',
        'card_condition',
        'condition_notes',
        'handling_notes',
        'performed_by_user_id',
        'performed_by_name_cached',
        'processed_at',
    ];

    public function visitorCard(){
        return $this->belongsTo(VisitorCard::class);
    }

    public function performedBy(){
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
