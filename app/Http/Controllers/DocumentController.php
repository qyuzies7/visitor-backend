<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(){
        return response()->json(Document::all());
    }

    public function store(Request $request){
        $validated = $request->validate([
            'visitor_card_id' => 'required|exists:visitor_cards,id',
            'file_path' => 'required|string|max:500',
            'file_name' => 'required|string|max:255',
            'file_size' => 'nullable|integer',
            'mime_type' => 'nullable|string|max:100',
        ]);
        $document = Document::create($validated);
        return response()->json($document, 201);
    }

    public function show($id){
        return response()->json(Document::findOrFail($id));
    }

    public function update(Request $request, $id){
        $document = Document::findOrFail($id);
        $validated = $request->validate([
            'visitor_card_id' => 'sometimes|exists:visitor_cards,id',
            'file_path' => 'sometimes|string|max:500',
            'file_name' => 'sometimes|string|max:255',
            'file_size' => 'nullable|integer',
            'mime_type' => 'nullable|string|max:100',
        ]);

        $document->update($validated);
        return response()->json($document);
    }

    public function destroy($id){
        $document = Document::findOrFail($id);
        $document->delete();
        return response()->json(['message' => 'Document deleted']);
    }
}
