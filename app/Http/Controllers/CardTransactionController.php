<?php

namespace App\Http\Controllers;

use App\Models\CardTransaction;
use Illuminate\Http\Request;

class CardTransactionController extends Controller
{
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
