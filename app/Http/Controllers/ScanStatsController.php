<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScanStatsController extends Controller
{
    public function getScanStats(Request $request)
    {
        $request->validate([
            'range' => 'sometimes|in:week,month,year'
        ]);

        $range = $request->input('range', 'week');

        if ($range === 'week') {
            return response()->json($this->getWeeklyStats());
        } elseif ($range === 'month') {
            return response()->json($this->getMonthlyStats());
        } elseif ($range === 'year') {
            return response()->json($this->getYearlyStats());
        }

        return response()->json(['error' => 'Invalid range parameter'], 400);
    }

    protected function getWeeklyStats()
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $daysOfWeek = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $successfulScans = [];
        $failedScans = [];

        for ($i = 0; $i < 7; $i++) {
            $dayStart = $startDate->copy()->addDays($i);
            $dayEnd = $dayStart->copy()->endOfDay();

            $successfulScans[] = Scan::where('status', 'valide')
                ->whereBetween('scanned_at', [$dayStart, $dayEnd])
                ->count();

            $failedScans[] = Scan::where('status', 'invalide')
                ->whereBetween('scanned_at', [$dayStart, $dayEnd])
                ->count();
        }

        return [
            'labels' => $daysOfWeek,
            'successfulScans' => $successfulScans,
            'failedScans' => $failedScans
        ];
    }

    protected function getMonthlyStats()
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $stats = Scan::select(
                DB::raw('DAY(scanned_at) as day'),
                DB::raw('SUM(CASE WHEN status = "valide" THEN 1 ELSE 0 END) as successful'),
                DB::raw('SUM(CASE WHEN status = "invalide" THEN 1 ELSE 0 END) as failed')
            )
            ->whereBetween('scanned_at', [$startDate, $endDate])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Initialiser les tableaux avec des zéros pour tous les jours du mois
        $successfulScans = array_fill(1, $daysInMonth, 0);
        $failedScans = array_fill(1, $daysInMonth, 0);

        // Remplir avec les données réelles
        foreach ($stats as $stat) {
            $successfulScans[$stat->day] = $stat->successful;
            $failedScans[$stat->day] = $stat->failed;
        }

        // Convertir les clés en tableau indexé à partir de 0
        $labels = range(1, $daysInMonth);
        $successfulScans = array_values($successfulScans);
        $failedScans = array_values($failedScans);

        return [
            'labels' => $labels,
            'successfulScans' => $successfulScans,
            'failedScans' => $failedScans
        ];
    }

    protected function getYearlyStats()
    {
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now()->endOfYear();

        $stats = Scan::select(
                DB::raw('MONTH(scanned_at) as month'),
                DB::raw('SUM(CASE WHEN status = "valide" THEN 1 ELSE 0 END) as successful'),
                DB::raw('SUM(CASE WHEN status = "invalide" THEN 1 ELSE 0 END) as failed')
            )
            ->whereBetween('scanned_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $successfulScans = array_fill(1, 12, 0);
        $failedScans = array_fill(1, 12, 0);

        foreach ($stats as $stat) {
            $successfulScans[$stat->month] = $stat->successful;
            $failedScans[$stat->month] = $stat->failed;
        }

        return [
            'labels' => $monthNames,
            'successfulScans' => array_values($successfulScans),
            'failedScans' => array_values($failedScans)
        ];
    }
}
