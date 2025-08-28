<?php

namespace App\Http\Controllers;

use App\Models\VisitorCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VisitorCardController extends Controller {

    // List pengajuan menunggu verifikasi
    public function getPending()
    {
        $pending = \App\Models\VisitorCard::where('status', 'processing')->get();
        return response()->json($pending);
    }

    // Detail pengajuan untuk review (by reference_number)
    public function detailByReference(Request $request)
    {
        $request->validate(['reference_number' => 'required|string']);
        $visitorCard = \App\Models\VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }
        return response()->json($visitorCard->getStatusDetail());
    }

    // Approve pengajuan
    public function approve(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string',
            'approval_notes' => 'nullable|string',
        ]);
        $visitorCard = \App\Models\VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }
        if (!$visitorCard->canApprove()) {
            return response()->json(['message' => 'Pengajuan tidak dapat disetujui'], 422);
        }
        $visitorCard->approveSubmission($request->approval_notes, auth()->user());
        return response()->json(['message' => 'Pengajuan disetujui']);
    }

    // Reject pengajuan
    public function reject(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string',
            'rejection_reason' => 'required|string',
        ]);
        $visitorCard = \App\Models\VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }
        if (!$visitorCard->canReject()) {
            return response()->json(['message' => 'Pengajuan tidak dapat ditolak'], 422);
        }
        $visitorCard->rejectSubmission($request->rejection_reason, auth()->user());
        return response()->json(['message' => 'Pengajuan ditolak']);
    }

    // Bulk approve/reject
    public function bulkAction(Request $request)
    {
        $request->validate([
            'actions' => 'required|array',
            'actions.*.reference_number' => 'required|string',
            'actions.*.action' => 'required|in:approve,reject',
            'actions.*.notes' => 'nullable|string',
        ]);
        $results = [];
        foreach ($request->actions as $action) {
            $visitorCard = \App\Models\VisitorCard::where('reference_number', $action['reference_number'])->first();
            if (!$visitorCard || $visitorCard->status !== 'processing') {
                $results[] = [
                    'reference_number' => $action['reference_number'],
                    'status' => 'failed',
                    'message' => 'Not found or not processing',
                ];
                continue;
            }
            if ($action['action'] === 'approve') {
                $visitorCard->approveSubmission($action['notes'] ?? null, auth()->user());
            } else {
                $visitorCard->rejectSubmission($action['notes'] ?? null, auth()->user());
            }
            $results[] = [
                'reference_number' => $action['reference_number'],
                'status' => 'success',
                'message' => 'Updated',
            ];
        }
        return response()->json($results);
    }
    // Cek status pengajuan
    public function checkStatus(Request $request)
    {
        $request->validate(['reference_number' => 'required|string']);
        $visitorCard = \App\Models\VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }
        return response()->json($visitorCard->getStatusDetail());
    }

    // Batalkan pengajuan
    public function cancel(Request $request)
    {
        $request->validate(['reference_number' => 'required|string']);
        $visitorCard = \App\Models\VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }
        if (!$visitorCard->canCancel()) {
            return response()->json(['message' => 'Pengajuan tidak dapat dibatalkan'], 422);
        }
        $visitorCard->cancelSubmission();
        return response()->json(['message' => 'Pengajuan berhasil dibatalkan']);
    }

    // Ajukan ulang pengajuan
    public function resubmit(Request $request)
    {
        $request->validate(['reference_number' => 'required|string']);
        $visitorCard = \App\Models\VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }
        if (!$visitorCard->canResubmit()) {
            return response()->json(['message' => 'Pengajuan tidak dapat diajukan ulang'], 422);
        }
        $visitorCard->resubmitSubmission();
        return response()->json(['message' => 'Pengajuan berhasil diajukan ulang']);
    }
    public function index()
    {
        return response()->json(VisitorCard::all());
    }

    public function show($id)
    {
    $visitorCard = VisitorCard::findOrFail($id);
    return response()->json($visitorCard->getStatusDetail());
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

        // Validasi durasi kunjungan 
        $visitType = \App\Models\VisitType::find($validated['visit_type_id']);
        if ($visitType) {
            $start = \Carbon\Carbon::parse($validated['visit_start_date']);
            $end = \Carbon\Carbon::parse($validated['visit_end_date']);
            $duration = $start->diffInDays($end) + 1;
            if ($duration > $visitType->max_duration_days) {
                return response()->json([
                    'message' => 'Durasi kunjungan melebihi batas maksimal untuk jenis kunjungan ini (' . $visitType->max_duration_days . ' hari)'
                ], 422);
            }
        }

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