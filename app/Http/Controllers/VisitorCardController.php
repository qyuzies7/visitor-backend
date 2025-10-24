<?php

namespace App\Http\Controllers;

use App\Models\VisitorCard;
use App\Models\VisitType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VisitorCardController extends Controller
{
    public function index(Request $request)
    {
        $status = strtolower($request->query('status', 'all'));

        $q = VisitorCard::query();

        if ($status === 'pending') {
            $q->where('status', 'processing');
        } elseif ($status === 'approved') {
            $q->where('status', 'approved');
        } elseif ($status === 'rejected') {
            $q->where('status', 'rejected');
        }

        $q->orderByDesc('created_at');

        $rows = $q->get();

        return response()->json($rows);
    }

    public function getApproved(Request $request)
    {
        $rows = VisitorCard::query()
            ->where('status', 'approved')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($rows);
    }

    public function getRejected(Request $request)
    {
        $rows = VisitorCard::query()
            ->where('status', 'rejected')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($rows);
    }

    public function getPending()
    {
        $pending = VisitorCard::where('status', 'processing')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($pending);
    }

    public function detailByReference(Request $request)
    {
        $reference = $request->input('reference_number', $request->input('reference'));

        if (!$reference || !is_string($reference)) {
            return response()->json(['message' => 'reference_number wajib diisi'], 422);
        }

        $visitorCard = VisitorCard::where('reference_number', $reference)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }

        $payload = $this->buildDetailPayload($visitorCard);

        return response()->json($payload);
    }

    public function approve(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string',
        ]);

        $visitorCard = VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }
        if (!$visitorCard->canApprove()) {
            return response()->json(['message' => 'Pengajuan tidak dapat disetujui'], 422);
        }

        $notes = $request->input('approval_notes', $request->input('note'));
        $visitorCard->approveSubmission($notes, auth()->user());

        return response()->json(['message' => 'Pengajuan disetujui']);
    }

    public function reject(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string',
        ]);

        $visitorCard = VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }
        if (!$visitorCard->canReject()) {
            return response()->json(['message' => 'Pengajuan tidak dapat ditolak'], 422);
        }

        $reason = $request->input('rejection_reason', $request->input('reason'));
        if (!$reason) {
            return response()->json(['message' => 'Alasan penolakan wajib diisi'], 422);
        }

        $visitorCard->rejectSubmission($reason, auth()->user());

        return response()->json(['message' => 'Pengajuan ditolak']);
    }

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
            $visitorCard = VisitorCard::where('reference_number', $action['reference_number'])->first();
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

    public function checkStatus(Request $request)
    {
        $request->validate(['reference_number' => 'required|string']);
        $visitorCard = VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }

        $payload = $this->buildDetailPayload($visitorCard);

        return response()->json($payload);
    }

    public function cancel(Request $request)
    {
        $request->validate(['reference_number' => 'required|string']);
        $visitorCard = VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }
        if (!$visitorCard->canCancel()) {
            return response()->json(['message' => 'Pengajuan tidak dapat dibatalkan'], 422);
        }
        $visitorCard->cancelSubmission();
        return response()->json(['message' => 'Pengajuan berhasil dibatalkan']);
    }

    public function resubmit(Request $request)
    {
        $request->validate(['reference_number' => 'required|string']);
        $visitorCard = VisitorCard::where('reference_number', $request->reference_number)->first();
        if (!$visitorCard) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }
        if (!$visitorCard->canResubmit()) {
            return response()->json(['message' => 'Pengajuan tidak dapat diajukan ulang'], 422);
        }
        $visitorCard->resubmitSubmission();
        return response()->json(['message' => 'Pengajuan berhasil diajukan ulang']);
    }

    public function show($id)
    {
        $visitorCard = VisitorCard::findOrFail($id);
        $payload = $this->buildDetailPayload($visitorCard);
        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'        => 'required|string|max:255',
            'institution'      => 'required|string|max:255',
            'email'            => 'required|email|max:255',
            'phone_number'     => 'required|string|max:32',
            'visit_type_id'    => 'required|exists:visit_types,id',
            'visit_start_date' => 'required|date',
            'visit_end_date'   => 'required|date|after_or_equal:visit_start_date',
            'station_id'       => 'nullable|exists:stations,id',
            'visit_purpose'    => 'required|string',
            'document'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',

            // === PIC & layanan ===
            'pic_name'               => ['nullable','string','max:255'],
            'pic_position'           => ['nullable','string','max:255'],
            'assistance_service'     => ['nullable','string','max:255'],

            // === Akses pintu ===
            'access_door'            => ['nullable','string','max:255'],
            'access_time'            => ['nullable','date_format:H:i'], 
            'access_purpose'         => ['nullable','string','max:500'],
            'vehicle_type'           => ['nullable','string','max:255'],
            'vehicle_plate'          => ['nullable','string','max:50'],
            'protokoler_count'       => ['nullable','integer','min:0'],
            'need_protokoler_escort' => ['nullable','boolean'],
        ]);

        // Validasi durasi kunjungan 
        $visitType = VisitType::find($validated['visit_type_id']);
        if ($visitType) {
            $start = Carbon::parse($validated['visit_start_date']);
            $end   = Carbon::parse($validated['visit_end_date']);
            $duration = $start->diffInDays($end) + 1;
            if ($duration > $visitType->max_duration_days) {
                return response()->json([
                    'message' => 'Durasi kunjungan melebihi batas maksimal untuk jenis kunjungan ini (' . $visitType->max_duration_days . ' hari)'
                ], 422);
            }
        }

        // Upload dokumen (opsional)
        if ($request->hasFile('document')) {
            $file         = $request->file('document');
            $originalName = $file->getClientOriginalName();
            $storedPath   = $file->store('documents', 'public');

            $validated['document_path']          = $storedPath;
            $validated['document_original_name'] = $originalName;
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
            'full_name'        => 'sometimes|string|max:255',
            'institution'      => 'sometimes|string|max:255',
            'email'            => 'sometimes|email|max:255',
            'phone_number'     => 'sometimes|string|max:32',
            'visit_type_id'    => 'sometimes|exists:visit_types,id',
            'visit_start_date' => 'sometimes|date',
            'visit_end_date'   => 'sometimes|date|after_or_equal:visit_start_date',
            'station_id'       => 'nullable|exists:stations,id',
            'visit_purpose'    => 'sometimes|string',
            'document'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',

            'status'           => 'sometimes|in:processing,approved,rejected,cancelled',
            'rejection_reason' => 'required_if:status,rejected|string|nullable',
            'approval_notes'   => 'required_if:status,approved|string|nullable',
            'last_updated_by_user_id'       => 'nullable|exists:users,id',
            'last_updated_by_name_cached'   => 'nullable|string|max:255',

            // === PIC & layanan (opsional, hanya jika dikirim) ===
            'pic_name'               => ['sometimes','nullable','string','max:255'],
            'pic_position'           => ['sometimes','nullable','string','max:255'],
            'assistance_service'     => ['sometimes','nullable','string','max:255'],

            // === Akses pintu (opsional, hanya jika dikirim) ===
            'access_door'            => ['sometimes','nullable','string','max:255'],
            'access_time'            => ['sometimes','nullable','date_format:H:i'], // "HH:MM"
            'access_purpose'         => ['sometimes','nullable','string','max:500'],
            'vehicle_type'           => ['sometimes','nullable','string','max:255'],
            'vehicle_plate'          => ['sometimes','nullable','string','max:50'],
            'protokoler_count'       => ['sometimes','nullable','integer','min:0'],
            'need_protokoler_escort' => ['sometimes','nullable','boolean'],
        ]);

        // Upload dokumen baru (opsional)
        if ($request->hasFile('document')) {
            if ($visitorCard->document_path) {
                Storage::disk('public')->delete($visitorCard->document_path);
            }

            $file         = $request->file('document');
            $originalName = $file->getClientOriginalName();
            $storedPath   = $file->store('documents', 'public');

            $validated['document_path']          = $storedPath;
            $validated['document_original_name'] = $originalName;
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

    public function downloadDocument(Request $request)
    {
        $path = $request->query('path');
        if (!$path) {
            return response()->json(['message' => 'Parameter path wajib diisi'], 422);
        }

        $rel = ltrim($path, '/');
        if (Str::startsWith($rel, 'storage/')) {
            $rel = substr($rel, strlen('storage/'));
        }

        if (!Storage::disk('public')->exists($rel)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }

        $originalName = basename($rel);
        $card = VisitorCard::where('document_path', $rel)
            ->orWhere('document_path', 'storage/' . $rel)
            ->first();

        if ($card && !empty($card->document_original_name)) {
            $originalName = $card->document_original_name;
        }

        return Storage::disk('public')->download($rel, $originalName);
    }

    /**
     * Bangun payload detail yang selalu menyertakan field baru (PIC & akses pintu),
     * plus metadata dokumen, di atas hasil getStatusDetail() (jika ada).
     */
    protected function buildDetailPayload(VisitorCard $visitorCard): array
    {
        $detail = $visitorCard->getStatusDetail();
        if (!is_array($detail)) {
            $detail = [];
        }

        // Pastikan field dokumen & field baru selalu ada
        $extra = [
            'document_original_name' => $visitorCard->document_original_name,
            'document_path'          => $visitorCard->document_path,

            // PIC & layanan
            'pic_name'               => $visitorCard->pic_name,
            'pic_position'           => $visitorCard->pic_position,
            'assistance_service'     => $visitorCard->assistance_service,

            // Akses pintu
            'access_door'            => $visitorCard->access_door,
            'access_time'            => $visitorCard->access_time,         // "HH:MM"
            'access_purpose'         => $visitorCard->access_purpose,
            'vehicle_type'           => $visitorCard->vehicle_type,        // "Jumlah & Jenis Kendaraan"
            'vehicle_plate'          => $visitorCard->vehicle_plate,       // Nopol
            'protokoler_count'       => $visitorCard->protokoler_count,    // int/null
            'need_protokoler_escort' => $visitorCard->need_protokoler_escort, // bool/null
        ];

        // merge: detail dari model (jika ada) ditimpa/ditambah oleh $extra agar pasti up-to-date
        return array_merge($detail, $extra);
    }
}
