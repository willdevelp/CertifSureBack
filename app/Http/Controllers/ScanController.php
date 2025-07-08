<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScanController extends Controller
{
    public function getAllScans()
    {
        // Récupérer toutes les données de la table scans
        $scans = DB::table('scans')->orderBy('scanned_at', 'desc')->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $scans,
        ]);
    }

    public function getScanStats()
    {
        // Compter les certificats valides et invalides
        $validCount = DB::table('scans')->where('status', 'valide')->count();
        $invalidCount = DB::table('scans')->where('status', 'invalide')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'valid' => $validCount,
                'invalid' => $invalidCount,
            ],
        ]);
    }
}
