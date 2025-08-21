<?php

namespace App\Http\Controllers;

use App\Models\VisitorCard;
use Illuminate\Http\Request;

class VisitorCardController extends Controller
{
    public function index(){
        return response()->json(VisitorCard::all());
    }

    public function store(Request $request){
        $validate = $request->validate([
            'reference_number' => 'required|string|max:32|unique:visitor_cards,reference_number',
            'full_name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'identity_number' => 'required|string|max:32',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:32',
            'visit_type_id' => 'required|exists:visit_types,id',
            'visit_start_date' => 'required|date',
            'visit_end_date' => 'required|date|after_or_equal:visit_start_date',
            'station_id' => 'nullable|exists:stations,id',
            'visit_purpose' => 'required|string',
            'status' => 'in:processing,approved,rejected,cancelled',
            'rejection_reason' => 'required_if:status,rejected|string',
            'approval_notes' => 'required_if:status,approved|string',
            'last_updated_by_user_cached' => 'nullable|exists:users,id',
            'last_updated_at' => 'nullable|date',
        ]);
    }
    public function update(Request $request, $id){
        $visitorCard = VisitorCard::findOrFail($id);
        $validated = $request->validate([
            'reference_number' => "sometimes|string|max:32|unique:visitor_cards,reference_number,$id",
            'full_name' => 'sometimes|string|max:255',
            'institution' => 'sometimes|string|max:255',
            'identity_number' => "sometimes|string|max:32|unique:visitor_cards,identity_number,$id",
            'email' => 'sometimes|email|max:255',
            'phone_number' => 'sometimes|string|max:32',
            'visit_type_id' => 'sometimes|exists:visit_types,id',
            'visit_start_date' => 'sometimes|date',
            'visit_end_date' => 'sometimes|date|after_or_equal:visit_start_date',
            'station_id' => 'nullable|exists:stations,id',
            'visit_purpose' => 'sometimes|string',
            'status' => 'sometimes|in:processing,approved,rejected,cancelled',
            'rejection_reason' => 'required_if:status,rejected|string',
            'approval_notes' => 'required_if:status,approved|string',
            'last_updated_by_user_cached' => 'nullable|exists:users,id',
            'last_updated_by_name_cached' => 'nullable|string|max:255',
            'last_updated_at' => 'nullable|date',
        ]);

        $visitorCard->update($validated);
        return response()->json($visitorCard);
    }
        public function destroy($id){
            $visitorCard = VisitorCard::findOrFail($id);
            $visitorCard->delete();
            return response()->json(['message' => 'Visitor Card deleted']);
        }
}
