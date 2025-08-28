<?php

namespace App\Http\Controllers;

use App\Models\CardTransaction;
use Illuminate\Http\Request;

class CardTransactionController extends Controller {

    // List kartu sudah dikembalikan
    public function listReturned()
    {
        $transactions = \App\Models\CardTransaction::returned()->with('visitorCard')->get();
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
        $trx = \App\Models\CardTransaction::findOrFail($id);
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
    public function issue(Request $request)
    {
        $request->validate([
            'visitor_card_id' => 'required|exists:visitor_cards,id',
            'notes' => 'nullable|string',
        ]);
        $card = \App\Models\VisitorCard::findOrFail($request->visitor_card_id);
        $trx = $card->issueCard($request->user(), $request->notes);
        return response()->json($trx);
    }

    // List kartu aktif (dipinjam)
    public function listActive()
    {
        $cards = \App\Models\VisitorCard::active()->get();
        return response()->json($cards);
    }

    // Terima kartu kembali (returned)
    public function return(Request $request)
    {
        $request->validate([
            'visitor_card_id' => 'required|exists:visitor_cards,id',
            'notes' => 'nullable|string',
        ]);
        $card = \App\Models\VisitorCard::findOrFail($request->visitor_card_id);
        $trx = $card->returnCard($request->user(), $request->notes);
        return response()->json($trx);
    }

    // Lapor kartu rusak
    public function reportDamaged(Request $request)
    {
        $request->validate([
            'visitor_card_id' => 'required|exists:visitor_cards,id',
            'notes' => 'nullable|string',
        ]);
        $card = \App\Models\VisitorCard::findOrFail($request->visitor_card_id);
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
        $card = \App\Models\VisitorCard::findOrFail($request->visitor_card_id);
        $trx = $card->reportLost($request->user(), $request->notes);
        return response()->json($trx);
    }
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
