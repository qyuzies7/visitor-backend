<?php

namespace App\Http\Controllers;

use App\Models\VisitorCardStatusLog;
use Illuminate\Http\Request;

class VisitorCardStatusLogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'visitor_card_id' => 'nullable|exists:visitor_cards,id',
            'reference_number' => 'nullable|string',
        ]);

        $query = VisitorCardStatusLog::query();
        if ($request->filled('visitor_card_id')) {
            $query->where('visitor_card_id', $request->visitor_card_id);
        } elseif ($request->filled('reference_number')) {
            $visitorCard = \App\Models\VisitorCard::where('reference_number', $request->reference_number)->first();
            if (!$visitorCard) {
                return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
            }
            $query->where('visitor_card_id', $visitorCard->id);
        }
        $logs = $query->orderBy('changed_at', 'desc')->get();
        return response()->json($logs);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'visitor_card_id' => 'required|exists:visitor_cards,id',
            'performed_by_user_id' => 'required|exists:users,id',
            'performed_by_name_cached' => 'required|string|max:255',   
            'old_status' => 'required|in:processing,approved,rejected,cancelled',
            'new_status' => 'required|in:processing,approved,rejected,cancelled',
            'notes' => 'nullable|string',
            'changed_at' => 'nullable|date', 
        ]);

        $log = VisitorCardStatusLog::create($validated);
        return response()->json($log, 201);
    }

    public function show($id){
        return response()->json(VisitorCardStatusLog::findOrFail($id));
    }

    public function update(Request $request, $id){
        $log = VisitorCardStatusLog::findOrFail($id);
        $validated = $request->validate([
            'visitor_card_id' => 'sometimes|exists:visitor_cards,id',
            'performed_by_user_id' => 'sometimes|exists:users,id',
            'performed_by_name_cached' => 'sometimes|string|max:255',
            'old_status' => 'sometimes|in:processing,approved,rejected,cancelled',
            'new_status' => 'sometimes|in:processing,approved,rejected,cancelled',
            'notes' => 'nullable|string',
            'changed_at' => 'nullable|date',
        ]);

        $log->update($validated);
        return response()->json($log);
    }

    public function destroy($id){
        $log = VisitorCardStatusLog::findOrFail($id);
        $log->delete();
        return response()->json(['message' => 'Visitor Card Status Log deleted successfully']);
    }

}
