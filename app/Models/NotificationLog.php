<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_card_id',
        'notification_type',
        'recipient',
        'message',
        'send_status',
        'error_message',
        'sent_at'
    ];

    public function visitorCard(){
        return $this->belongsTo(VisitorCard::class);
    }
}
