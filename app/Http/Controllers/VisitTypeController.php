<?php

namespace App\Http\Controllers;

use App\Models\VisitType;
use Illuminate\Http\Request;

class VisitTypeController extends Controller
{
    public function index(){
        return response()->json(VisitType::all());
    }

    public function store(Request $request){
        $validated = $request->validate([
            'type_name' => 'required|string|max:100|unique:visit_types,type_name',
            'max_duration_days' => 'required|integer',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $visitType = VisitType::create($validated);
        return response()->json($visitType, 201);
    }

    public function show($id){
        $visitType = VisitType::findOrFail($id);
        return response()->json($visitType);
    }

    public function update(Request $request, $id){
        $visitType = VisitType::findOrFail($id);
        $validated = $request->validate([
            'type_name' => "sometimes|string|max:100|unique:visit_types,type_name,$id",
            'max_duration_days' => 'sometimes|integer',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $visitType->update($validated);
        return response()->json($visitType);
    }

    public function destroy($id){
        $visitType = VisitType::findOrFail($id);
        $visitType->delete();
        return response()->json(['message' => 'Visit Type deleted']);
    }
}

