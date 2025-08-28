<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class VisitorCard extends Model {

    // Scope kartu yang disetujui
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope kartu 
    public function scopeActive($query)
    {
        return $query->where('status', 'approved')->whereHas('cardTransactions', function($q) {
            $q->where('transaction_type', 'issued');
        })->whereDoesntHave('cardTransactions', function($q) {
            $q->whereIn('transaction_type', ['returned', 'lost', 'damaged']);
        });
    }

    // Helper serahkan kartu
    public function issueCard($user, $notes = null)
    {
        return $this->cardTransactions()->create([
            'transaction_type' => 'issued',
            'card_condition' => 'good',
            'condition_notes' => $notes,
            'performed_by_user_id' => $user->id,
            'performed_by_name_cached' => $user->full_name,
            'processed_at' => now(),
        ]);
    }

    // Helper terima kartu kembali
    public function returnCard($user, $notes = null)
    {
        return $this->cardTransactions()->create([
            'transaction_type' => 'returned',
            'card_condition' => 'good',
            'condition_notes' => $notes,
            'performed_by_user_id' => $user->id,
            'performed_by_name_cached' => $user->full_name,
            'processed_at' => now(),
        ]);
    }

    // Helper lapor kartu rusak
    public function reportDamaged($user, $notes = null)
    {
        return $this->cardTransactions()->create([
            'transaction_type' => 'damaged',
            'card_condition' => 'damaged',
            'condition_notes' => $notes,
            'performed_by_user_id' => $user->id,
            'performed_by_name_cached' => $user->full_name,
            'processed_at' => now(),
        ]);
    }

    // Helper lapor kartu hilang
    public function reportLost($user, $notes = null)
    {
        return $this->cardTransactions()->create([
            'transaction_type' => 'lost',
            'card_condition' => 'lost',
            'condition_notes' => $notes,
            'performed_by_user_id' => $user->id,
            'performed_by_name_cached' => $user->full_name,
            'processed_at' => now(),
        ]);
    }

    public function canApprove()
    {
        return $this->status === 'processing';
    }

    // Approve pengajuan
    public function approveSubmission($notes = null, $user = null)
    {
        $oldStatus = $this->status;
        $this->status = 'approved';
        $this->approval_notes = $notes;
        $this->save();
        $this->statusLogs()->create([
            'performed_by_user_id' => $user ? $user->id : null,
            'performed_by_name_cached' => $user ? $user->full_name : 'admin',
            'old_status' => $oldStatus,
            'new_status' => 'approved',
            'notes' => $notes,
            'changed_at' => now(),
        ]);
        // Auto send notification (email/wa)
        \App\Models\NotificationLog::sendAutoNotification($this, 'approved');
    }

    public function canReject()
    {
        return $this->status === 'processing';
    }

    // Reject pengajuan
    public function rejectSubmission($reason, $user = null)
    {
        $oldStatus = $this->status;
        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->save();
        $this->statusLogs()->create([
            'performed_by_user_id' => $user ? $user->id : null,
            'performed_by_name_cached' => $user ? $user->full_name : 'admin',
            'old_status' => $oldStatus,
            'new_status' => 'rejected',
            'notes' => $reason,
            'changed_at' => now(),
        ]);
        // Auto send notification (email/wa)
        \App\Models\NotificationLog::sendAutoNotification($this, 'rejected');
    }
    // Detail status pengajuan
    public function getStatusDetail()
    {
        return [
            'reference_number' => $this->reference_number,
            'full_name' => $this->full_name,
            'institution' => $this->institution,
            'identity_number' => $this->identity_number,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'visit_type' => $this->visitType ? $this->visitType->type_name : null,
            'visit_start_date' => $this->visit_start_date,
            'visit_end_date' => $this->visit_end_date,
            'station' => $this->station ? $this->station->station_name : null,
            'visit_purpose' => $this->visit_purpose,
            'document_url' => $this->document_path ? url('storage/' . $this->document_path) : null,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'approval_notes' => $this->approval_notes,
            'last_updated_at' => $this->last_updated_at,
        ];
    }

    // Cek apakah bisa cancel
    public function canCancel()
    {
        return $this->status === 'processing';
    }

    // Batalkan pengajuan
    public function cancelSubmission()
    {
        $this->status = 'cancelled';
        $this->save();
    }

    // Cek apakah bisa resubmit
    public function canResubmit()
    {
        return in_array($this->status, ['rejected', 'cancelled']);
    }

    // Ajukan ulang
    public function resubmitSubmission()
    {
        $this->status = 'processing';
        $this->rejection_reason = null;
        $this->approval_notes = null;
        $this->save();
    }
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