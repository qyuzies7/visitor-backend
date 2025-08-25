<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

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
        'document_path',
        'status',
        'rejection_reason',
        'approval_notes',
        'last_updated_by_user_id',
        'last_updated_by_name_cached',
        'last_updated_at',
    ];

    protected $casts = [
        'visit_start_date' => 'date',
        'visit_end_date' => 'date',
        'last_updated_at' => 'datetime',
    ];


    //generate nomor referensi
    public static function generateReferenceNumber(): string
    {
        do {
            $ref = 'VST-' . strtoupper(Str::random(8));
        } while (self::where('reference_number', $ref)->exists());
        
        return $ref;
    }

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

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class, 'visitor_card_id');
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_path ? url('storage/' . $this->document_path) : null;
    }
}