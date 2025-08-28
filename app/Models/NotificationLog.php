<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationLog extends Model {

    /**
     * Kirim notifikasi otomatis (log + stub email/wa)
     * @param VisitorCard $visitorCard
     * @param string $status 'approved'|'rejected'
     */
    public static function sendAutoNotification($visitorCard, $status)
    {
        $type = $status === 'approved' ? 'Diterima' : 'Ditolak';
        $message = $status === 'approved'
            ? "Pengajuan visitor card Anda telah DITERIMA. Ref: {$visitorCard->reference_number}"
            : "Pengajuan visitor card Anda DITOLAK. Ref: {$visitorCard->reference_number}. Alasan: {$visitorCard->rejection_reason}";

        // Email
        $emailLog = self::create([
            'visitor_card_id' => $visitorCard->id,
            'notification_type' => 'email',
            'recipient' => $visitorCard->email,
            'message' => $message,
            'send_status' => 'pending',
            'sent_at' => null,
        ]);
        try {
            \Mail::to($visitorCard->email)->send(new \App\Mail\NotifikasiVisitorCard($message));
            $emailLog->send_status = 'sent';
            $emailLog->sent_at = now();
            $emailLog->save();
        } catch (\Exception $e) {
            $emailLog->send_status = 'failed';
            $emailLog->error_message = $e->getMessage();
            $emailLog->save();
        }

        // WhatsApp (Twilio)
        if ($visitorCard->phone_number) {
            $waLog = self::create([
                'visitor_card_id' => $visitorCard->id,
                'notification_type' => 'whatsapp',
                'recipient' => $visitorCard->phone_number,
                'message' => $message,
                'send_status' => 'pending',
                'sent_at' => null,
            ]);
            try {
                if (class_exists('Twilio\\Rest\\Client')) {
                    $sid = config('services.twilio.sid');
                    $token = config('services.twilio.token');
                    $from = config('services.twilio.whatsapp_from');
                    $twilio = new \Twilio\Rest\Client($sid, $token);
                    $twilio->messages->create(
                        'whatsapp:' . $visitorCard->phone_number,
                        [
                            'from' => 'whatsapp:' . $from,
                            'body' => $message
                        ]
                    );
                    $waLog->send_status = 'sent';
                    $waLog->sent_at = now();
                    $waLog->save();
                } else {
                    throw new \Exception('Twilio SDK not installed');
                }
            } catch (\Exception $e) {
                $waLog->send_status = 'failed';
                $waLog->error_message = $e->getMessage();
                $waLog->save();
            }
        }
    }
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
