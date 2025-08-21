<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitorCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'full_name',
        'institution',
        'identity_number',
        'email',
        'phone_number',
        'visit_type_id',
        'visit_start_date',
        'visit_end_date',
        'station_id',
        'visit_purpose',
        'status',
        'rejection_reason',
        'approval_notes',
        'last_updated_by_user_id',
        'last_updated_by_name_cached',
        'last_updated_at',
    ];

    public function visitType()
    {
        return $this->belongsTo(VisitType::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by_user_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(VisitorCardStatusLog::class);
    }

    public function cardTransactions()
    {
        return $this->hasMany(CardTransaction::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class, 'visitor_card_id');
    }
}