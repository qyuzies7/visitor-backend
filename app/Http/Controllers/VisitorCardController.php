<?php

namespace App\Http\Controllers;

use App\Models\VisitorCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VisitorCardController extends Controller
{
    public function index()
    {
        return response()->json(VisitorCard::all());
    }

    public function show($id)
    {
        $visitorCard = VisitorCard::findOrFail($id);
        return response()->json($visitorCard);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'identity_number' => 'required|string|max:32|unique:visitor_cards,identity_number',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:32',
            'visit_type_id' => 'required|exists:visit_types,id',
            'visit_start_date' => 'required|date',
            'visit_end_date' => 'required|date|after_or_equal:visit_start_date',
            'station_id' => 'nullable|exists:stations,id',
            'visit_purpose' => 'required|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('visitor_documents', 'public');
            $validated['document_path'] = $path;
        }

        $validated['reference_number'] = VisitorCard::generateReferenceNumber();
        $validated['status'] = 'processing';
        
        $visitorCard = VisitorCard::create($validated);
        return response()->json($visitorCard, 201);
    }

    public function update(Request $request, $id)
    {
        $visitorCard = VisitorCard::findOrFail($id);
        
        $validated = $request->validate([
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
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'status' => 'sometimes|in:processing,approved,rejected,cancelled',
            'rejection_reason' => 'required_if:status,rejected|string|nullable',
            'approval_notes' => 'required_if:status,approved|string|nullable',
            'last_updated_by_user_id' => 'nullable|exists:users,id',
            'last_updated_by_name_cached' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('document')) {
            if ($visitorCard->document_path) {
                Storage::disk('public')->delete($visitorCard->document_path);
            }
            $path = $request->file('document')->store('visitor_documents', 'public');
            $validated['document_path'] = $path;
        }

        $validated['last_updated_at'] = now();
        
        $visitorCard->update($validated);
        return response()->json($visitorCard);
    }

    public function destroy($id)
    {
        $visitorCard = VisitorCard::findOrFail($id);
        
        if ($visitorCard->document_path) {
            Storage::disk('public')->delete($visitorCard->document_path);
        }
        
        $visitorCard->delete();
        return response()->json(['message' => 'Visitor Card deleted successfully']);
    }
}