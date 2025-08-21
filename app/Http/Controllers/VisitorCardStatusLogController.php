<?php

namespace App\Http\Controllers;

use App\Models\VisitorCardStatusLog;
use Illuminate\Http\Request;

class VisitorCardStatusLogController extends Controller
{
    public function index(){
        return response()->json(VisitorCardStatusLog::all());
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
