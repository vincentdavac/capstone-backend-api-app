<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\recent_alerts;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class fetchAlerts extends Controller
{
    public function getAlerts(Request $request){
        $user = $request->query('barangay_id');
        $recorded = Carbon::now('Asia/Manila')->subMinutes(2)->format('Y-m-d H:i:s');
        $getAllAlerts = DB::table('recent_alerts')
            ->join('buoys', 'recent_alerts.buoy_id', '=', 'buoys.id')
            ->where('buoys.barangay_id', $user ?? 5)
            ->where('recent_alerts.alert_level', '!=', 'White')
            ->select('recent_alerts.*')
            ->orderBy('recent_alerts.recorded_at', 'asc')
            ->get();
        return response()->json(['status' => true, 'data' => $getAllAlerts], 200, [], JSON_PRETTY_PRINT);
    }
    public function generateReport(Request $request){
        try {
            $request->validate([
                'barangay_id' => 'required',
                'from'=> 'required|date',
                'to' => 'required|date|after_or_equal:from',
            ]);

            $from = Carbon::parse($request->from);
            $to = Carbon::parse($request->to);
            $alerts = DB::table('recent_alerts')
                ->join('buoys', 'recent_alerts.buoy_id', '=', 'buoys.id')
                ->where('buoys.barangay_id', $request->barangay_id)
                ->whereBetween('recent_alerts.recorded_at', [$from, $to])
                ->select('recent_alerts.*')
                ->orderBy('recent_alerts.recorded_at', 'asc')
                ->get();

            if ($alerts->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No alerts found for selected date range.',
                ], 404);
            }
            $totalAlerts = $alerts->count();
            $redAlerts = $alerts->where('alert_level', 'Red')->count();
            $blueAlerts = $alerts->where('alert_level', 'Blue')->count();
            $whiteAlerts  = $alerts->where('alert_level', 'White')->count();
            $barangay = DB::table('barangays')->where('id', $request->barangay_id)->first();
            $barangayName = $barangay ? $barangay->name : 'Barangay';
            $user = Auth::user();
            $generatedBy   = $user ? $user->first_name . ' ' . $user->last_name : 'System';
            $fromFormatted = $from->format('F d Y - h:i A');
            $toFormatted   = $to->format('F d Y - h:i A');
            $generatedDate = Carbon::now()->format('F d Y - h:i A');

            $pdf = Pdf::loadView('reports.alert-historical-report', [
                'alerts'  => $alerts,
                'totalAlerts' => $totalAlerts,
                'redAlerts' => $redAlerts,
                'blueAlerts'  => $blueAlerts,
                'whiteAlerts' => $whiteAlerts,
                'barangayName'  => $barangayName,
                'fromFormatted' => $fromFormatted,
                'toFormatted'  => $toFormatted,
                'generatedDate' => $generatedDate,
                'generatedBy' => $generatedBy,
            ])->setPaper('a4', 'portrait');

            return $pdf->download('Alert_Historical_Report.pdf');

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}