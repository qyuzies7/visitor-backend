<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    public function index(){
        return response()->json(NotificationLog::all());
    }

    public function store(Request $request){
        $validated = $request->validate([
            'visitor_card_id' => 'required|exists:visitor_cards,id',
            'notification_type' => 'required|in:email,whatsapp',
            'recipient' => 'required|string|max:255',
            'message' => 'required|string',
            'send_status' => 'in:pending,sent,failed',
            'error_message' => 'nullable|string',
            'sent_at' => 'nullable|date',
        ]);
        $notificationLog = NotificationLog::create($validated);
        return response()->json($notificationLog, 201);
    }

    public function show($id){
        return response()->json(NotificationLog::findOrFail($id));
    }

    public function update(Request $request, $id){
        $notificationLog = NotificationLog::findOrFail($id);
        $validated = $request->validate([
            'visitor_card_id' => 'sometimes|exists:visitor_cards,id',
            'notification_type' => 'sometimes|in:email,whatsapp',
            'recipient' => 'sometimes|string|max:255',
            'message' => 'sometimes|string',
            'send_status' => 'sometimes|in:pending,sent,failed',
            'error_message' =>
            'nullable|string',
            'sent_at' => 'nullable|date',
        ]);

        $notif->update($validated);
        return response()->json($notif);
    }

    public function destroy($id){
        $notificationLog = NotificationLog::findOrFail($id);
        $notificationLog->delete();
        return response()->json(['message' => 'Notification log deleted']);
    }
}
