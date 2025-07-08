<?php

namespace App\Http\Controllers;
use App\Models\Promotions; // Ensure the Promotion model exists in the App\Models namespace
use Illuminate\Http\Request;

class PromotionsController extends Controller
{
    public function index()
    {
        $promotions = Promotions::pluck('nom');
        return response()->json($promotions);
    }

    public function show($id)
    {
        $promotion = Promotions::findOrFail($id);
        return response()->json($promotion);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'referentiel' => 'required|string|max:255',
            'date_start' => 'required|date',
            'date_end' => 'required|date',
            'date_exp' => 'required|date',
        ]);

        $promotion = Promotions::create($request->all());
        return response()->json($promotion, 201);
    }

    public function update(Request $request, $id)
    {
        // Logic to update an existing promotion
    }

    public function destroy($id)
    {
        // Logic to delete a promotion
        $promotion = Promotions::findOrFail($id);
        $promotion->delete();
        return response()->json(['message' => 'Promotion deleted'], 204);
    }
}
