<?php

namespace App\Http\Controllers;

use App\Models\CardTransaction;
use App\Models\VisitorCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CardTransactionController extends Controller {

    // List kartu sudah dikembalikan
    public function listReturned()
    {
        $transactions = CardTransaction::returned()->with('visitorCard')->get();
        return response()->json($transactions);
    }

    // Edit kondisi kartu
    public function editCondition(Request $request, $id)
    {
        $request->validate([
            'card_condition' => 'required|in:good,damaged,lost',
            'condition_notes' => 'nullable|string',
            'handling_notes' => 'nullable|string',
        ]);
        $trx = CardTransaction::findOrFail($id);
        $trx->updateCondition($request->card_condition, $request->condition_notes, $request->handling_notes);
        return response()->json($trx);
    }

    // List kartu yang disetujui (belum pernah issued)
    public function listApproved()
    {
        $cards = \App\Models\VisitorCard::approved()
            ->whereDoesntHave('cardTransactions', function($q){ $q->where('transaction_type', 'issued'); })
            ->get();
        return response()->json($cards);
    }

    // Serahkan kartu (issued)
    // Menerima visitor_card_id OR reference_number
    public function issue(Request $request)
    {
        // terima body yang mungkin kosong string dan hapus yang kosong
        $request->merge(array_filter($request->all(), function ($v) { return $v !== ''; }));

        if (!$request->filled('visitor_card_id') && !$request->filled('reference_number')) {
            return response()->json(['message' => 'visitor_card_id atau reference_number wajib diisi'], 422);
        }

        // cari visitor card
        if ($request->filled('visitor_card_id')) {
            $card = VisitorCard::find($request->input('visitor_card_id'));
            if (!$card) return response()->json(['message' => 'Visitor card tidak ditemukan'], 404);
        } else {
            $ref = $request->input('reference_number');
            $card = VisitorCard::where('reference_number', $ref)->first();
            if (!$card) return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
            if ($card->status !== 'approved') {
                return response()->json(['message' => 'Pengajuan belum disetujui'], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Cek apakah sudah ada issued tanpa returned -> kalau sudah, kembalikan existing
            $existing = $card->cardTransactions()
                ->where('transaction_type', 'issued')
                ->whereNull('processed_at') // if your logic uses processed_at; keep flexible
                ->orWhere(function($q){
                    $q->where('transaction_type', 'issued');
                })
                ->orderByDesc('id')
                ->first();

            // Better logic: check if there is an issued transaction and no later returned for same card.
            // We'll determine "already active" by checking if there is an issued transaction with no corresponding returned transaction after it.
            $activeIssued = null;
            $issuedTx = $card->cardTransactions()->where('transaction_type', 'issued')->orderByDesc('id')->first();
            if ($issuedTx) {
                $returnedAfter = $card->cardTransactions()
                    ->where('transaction_type', 'returned')
                    ->where('id', '>', $issuedTx->id)
                    ->exists();
                if (!$returnedAfter) {
                    $activeIssued = $issuedTx;
                }
            }

            if ($activeIssued) {
                DB::commit();
                return response()->json([
                    'message' => 'Kartu sudah diserahkan sebelumnya',
                    'data' => [
                        'card_transaction_id' => $activeIssued->id,
                        'visitor_card_id' => $card->id,
                        'transaction' => $activeIssued,
                    ]
                ], 200);
            }

            // Buat transaksi issued via helper VisitorCard->issueCard
            $notes = $request->input('notes') ?? $request->input('condition_notes') ?? null;
            $trx = $card->issueCard($request->user(), $notes);

            DB::commit();

            return response()->json([
                'message' => 'Kartu berhasil diserahkan',
                'data' => [
                    'card_transaction_id' => $trx->id,
                    'visitor_card_id' => $card->id,
                    'transaction' => $trx,
                ]
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Issue card failed: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memproses penyerahan kartu'], 500);
        }
    }

    // List kartu aktif (dipinjam)
    public function listActive()
    {
        $cards = \App\Models\VisitorCard::active()->get();
        return response()->json($cards);
    }

    // Terima kartu kembali (returned)
    // Menerima visitor_card_id OR card_transaction_id OR reference_number
    public function return(Request $request)
    {
        $request->merge(array_filter($request->all(), function ($v) { return $v !== ''; }));

        if (!$request->filled('visitor_card_id') && !$request->filled('card_transaction_id') && !$request->filled('reference_number')) {
            return response()->json(['message' => 'visitor_card_id atau card_transaction_id atau reference_number wajib diisi'], 422);
        }

        $visitorCard = null;
        $notes = $request->input('notes') ?? $request->input('condition_notes') ?? null;

        if ($request->filled('visitor_card_id')) {
            $visitorCard = VisitorCard::find($request->input('visitor_card_id'));
            if (!$visitorCard) return response()->json(['message' => 'Visitor card tidak ditemukan'], 404);
        } elseif ($request->filled('card_transaction_id')) {
            $tx = CardTransaction::find($request->input('card_transaction_id'));
            if (!$tx) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
            $visitorCard = $tx->visitorCard;
            if (!$visitorCard) return response()->json(['message' => 'Visitor card terkait transaksi tidak ditemukan'], 404);
        } elseif ($request->filled('reference_number')) {
            $ref = $request->input('reference_number');
            $visitorCard = VisitorCard::where('reference_number', $ref)->first();
            if (!$visitorCard) return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            // Pastikan ada issued transaction sebelumnya (opsional)
            $lastIssued = $visitorCard->cardTransactions()->where('transaction_type', 'issued')->orderByDesc('id')->first();
            if (!$lastIssued) {
                // masih bisa memproses return, tapi beri peringatan
                // return response()->json(['message' => 'Tidak ditemukan transaksi penyerahan (issued) untuk kartu ini'], 422);
            } else {
                // cek apakah sudah ada returned after this issue
                $returnedAfter = $visitorCard->cardTransactions()
                    ->where('transaction_type', 'returned')
                    ->where('id', '>', $lastIssued->id)
                    ->exists();
                if ($returnedAfter) {
                    // sudah dikembalikan
                    DB::commit();
                    return response()->json(['message' => 'Kartu sudah dikembalikan sebelumnya'], 422);
                }
            }

            // Buat transaksi returned via helper VisitorCard->returnCard
            $trx = $visitorCard->returnCard($request->user(), $notes);

            DB::commit();

            return response()->json([
                'message' => 'Kartu berhasil diterima',
                'data' => [
                    'card_transaction_id' => $trx->id,
                    'visitor_card_id' => $visitorCard->id,
                    'transaction' => $trx,
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Return card failed: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memproses pengembalian kartu'], 500);
        }
    }

    // Lapor kartu rusak
    public function reportDamaged(Request $request)
    {
        $request->validate([
            'visitor_card_id' => 'required|exists:visitor_cards,id',
            'notes' => 'nullable|string',
        ]);
        $card = VisitorCard::findOrFail($request->visitor_card_id);
        $trx = $card->reportDamaged($request->user(), $request->notes);
        return response()->json($trx);
    }

    // Lapor kartu hilang
    public function reportLost(Request $request)
    {
        $request->validate([
            'visitor_card_id' => 'required|exists:visitor_cards,id',
            'notes' => 'nullable|string',
        ]);
        $card = VisitorCard::findOrFail($request->visitor_card_id);
        $trx = $card->reportLost($request->user(), $request->notes);
        return response()->json($trx);
    }

    // index/store/show/update/destroy kept as-is or existing implementations
    public function index(){
        return response()->json(CardTransaction::all());
    }

    public function store(Request $request){
        $validated = $request->validate([
            'visitor_card_id' => 'required|exists:visitor_cards,id',
            'transaction_type' => 'required|string|in:issued,returned,damaged,lost',
            'card_condition' => 'required|string|in:good,damaged,lost',
            'condition_notes' => 'nullable|string',
            'handling_notes' => 'nullable|string',
            'performed_by_user_id' => 'required|exists:users,id',
            'performed_by_name_cached' => 'required|string|max:255',
            'processed_at' => 'nullable|date',
        ]);

        $transaction = CardTransaction::create($validated);
        return response()->json($transaction, 201);
    }

    public function show($id){
        return response()->json(CardTransaction::findOrFail($id));
    }

    public function update(Request $request, $id){
        $transaction = CardTransaction::findOrFail($id);
        $validated = $request->validate([
            'visitor_card_id' => 'sometimes|exists:visitor_cards,id',
            'transaction_type' => 'sometimes|string|in:issued,returned,damaged,lost',
            'card_condition' => 'sometimes|string|in:good,damaged,lost',
            'condition_notes' => 'nullable|string',
            'handling_notes' => 'nullable|string',
            'performed_by_user_id' => 'sometimes|exists:users,id',
            'performed_by_name_cached' => 'sometimes|string|max:255',
            'processed_at' => 'nullable|date',
        ]);

        $transaction->update($validated);
        return response()->json($transaction);
    }

    public function destroy($id){
        $transaction = CardTransaction::findOrFail($id);
        $transaction->delete();
        return response()->json(['message' => 'Card Transaction deleted']);
    }
}