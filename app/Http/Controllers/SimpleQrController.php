<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\QrCode;
use Carbon\Carbon;

class SimpleQrController extends Controller
{
    public function showScanner()
    {
        return view('simple-qr-scanner');
    }

    public function processScan(Request $request)
    {
        try {
            $request->validate([
                'qr_data' => 'required|string'
            ]);

            $user = Auth::user();
            
            if (!$user->employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not registered as an employee.'
                ]);
            }

            // Simple QR validation - just check if it exists
            $qrCode = QrCode::where('token', $request->qr_data)->first();
            
            if (!$qrCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code.'
                ]);
            }

            $today = Carbon::today();
            $attendance = Attendance::where('employee_id', $user->employee->id)
                ->whereDate('created_at', $today)
                ->first();

            if (!$attendance) {
                // Check in
                $attendance = Attendance::create([
                    'employee_id' => $user->employee->id,
                    'check_in' => now(),
                    'status' => 'present'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Check-in successful!',
                    'redirect' => route('dashboard')
                ]);
            } elseif (!$attendance->check_out) {
                // Check out
                $attendance->update([
                    'check_out' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Check-out successful!',
                    'redirect' => route('dashboard')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Already checked in and out today.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}