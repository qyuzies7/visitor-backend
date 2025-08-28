<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function index(){
        return response()->json(Station::all());
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'station_name' => 'required|string|max:255',
            'station_code' => 'required|string|max:100|unique:stations,station_code',
            'address' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $station = Station::create($validatedData);
        return response()->json($station, 201);
    }

    public function show($id){
        $station = Station::findOrFail($id);
        return response()->json($station);
    }

    public function update(Request $request, $id){
        $station = Station::findOrFail($id);
        $validated = $request->validate([
            'station_name' => 'required|string|max:255',
            'station_code' => 'required|string|max:100|unique:stations,station_code,' . $station->id,
            'address' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $station->update($validated);
        return response()->json($station);
    }

    public function destroy($id){
        $station = Station::findOrFail($id);
        $station->delete();
        return response()->json(['message' => 'Station deleted']);
    }
}
