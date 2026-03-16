<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::with(['employee.user', 'employee.department']);

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('check_in', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('check_in', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by department (for admin/manager)
        if ($user->isAdmin() && $request->has('department_id') && $request->department_id) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        } elseif ($user->isManager()) {
            // Managers only see their department
            $departmentId = $user->employee->department_id;
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        } elseif ($user->isEmployee()) {
            // Employees only see their own attendance
            $query->where('employee_id', $user->employee->id);
        }

        // Search by employee name or ID
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('employee.user', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            })->orWhereHas('employee', function($q) use ($search) {
                $q->where('employee_id', 'LIKE', "%{$search}%");
            });
        }

        $attendances = $query->latest('check_in')->paginate(15);
        
        // Get departments for filter (admin only)
        $departments = [];
        if ($user->isAdmin()) {
            $departments = \App\Models\Department::all();
        }

        return view('attendances.index', compact('attendances', 'departments'));
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create()
    {
        $employees = Employee::with('user')->whereHas('user', function($q) {
            $q->where('is_active', true);
        })->get();
        
        return view('attendances.create', compact('employees'));
    }

    /**
     * Store a newly created attendance record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'check_in' => 'required|date',
            'check_out' => 'nullable|date|after:check_in',
            'status' => 'required|in:present,absent,late,half-day,overtime',
            'notes' => 'nullable|string',
        ]);

        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'check_in_method' => 'manual',
            'status' => $request->status,
            'notes' => $request->notes,
            'date' => Carbon::parse($request->check_in)->toDateString(),
        ]);

        if ($request->check_out) {
            $this->calculateWorkHours($attendance);
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record created successfully.');
    }

    /**
     * Display the specified attendance record.
     */
    public function show(Attendance $attendance)
    {
        $user = Auth::user();
        
        // Check if user can view this attendance
        if (!$user->isAdmin() && !$user->isManager() && $user->employee->id !== $attendance->employee_id) {
            abort(403, 'Unauthorized to view this attendance record.');
        }
        
        // If manager, check department
        if ($user->isManager() && $user->employee->department_id !== $attendance->employee->department_id) {
            abort(403, 'Unauthorized to view this attendance record.');
        }
        
        $attendance->load(['employee.user', 'employee.department']);
        
        return view('attendances.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Attendance $attendance)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Unauthorized to edit attendance records.');
        }
        
        // Managers can only edit their department
        if ($user->isManager() && $user->employee->department_id !== $attendance->employee->department_id) {
            abort(403, 'Unauthorized to edit this attendance record.');
        }
        
        $employees = Employee::with('user')->whereHas('user', function($q) {
            $q->where('is_active', true);
        })->get();
        
        return view('attendances.edit', compact('attendance', 'employees'));
    }

    /**
     * Update the specified attendance record.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Unauthorized to update attendance records.');
        }
        
        // Managers can only update their department
        if ($user->isManager() && $user->employee->department_id !== $attendance->employee->department_id) {
            abort(403, 'Unauthorized to update this attendance record.');
        }

        $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'nullable|date|after:check_in',
            'status' => 'required|in:present,absent,late,half-day,overtime',
            'notes' => 'nullable|string',
        ]);

        $attendance->update([
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'status' => $request->status,
            'notes' => $request->notes,
            'date' => Carbon::parse($request->check_in)->toDateString(),
        ]);

        if ($request->check_out) {
            $this->calculateWorkHours($attendance);
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(Attendance $attendance)
    {
        // Only admin can delete
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized to delete attendance records.');
        }
        
        $attendance->delete();

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record deleted successfully.');
    }

    /**
     * Display attendance for a specific employee.
     */
    public function employeeAttendance(Employee $employee)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->isAdmin() && !$user->isManager() && $user->employee->id !== $employee->id) {
            abort(403, 'Unauthorized to view this employee\'s attendance.');
        }
        
        // Managers can only view their department
        if ($user->isManager() && $user->employee->department_id !== $employee->department_id) {
            abort(403, 'Unauthorized to view this employee\'s attendance.');
        }
        
        $attendances = Attendance::where('employee_id', $employee->id)
            ->latest('check_in')
            ->paginate(15);
        
        return view('attendances.employee', compact('employee', 'attendances'));
    }

    /**
     * Export attendance records.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        // Only admin and manager can export
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Unauthorized to export attendance records.');
        }
        
        $query = Attendance::with(['employee.user', 'employee.department']);

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('check_in', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('check_in', '<=', $request->end_date);
        }

        // If manager, only export their department
        if ($user->isManager()) {
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('department_id', $user->employee->department_id);
            });
        }

        $attendances = $query->get();

        // Generate CSV
        $filename = 'attendance_export_' . Carbon::now()->format('Y_m_d_H_i_s') . '.csv';
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add headers
        fputcsv($handle, [
            'Employee ID',
            'Employee Name',
            'Department',
            'Date',
            'Check In',
            'Check Out',
            'Status',
            'Work Hours',
            'Check In Method',
            'Check Out Method',
            'Notes'
        ]);

        // Add data
        foreach ($attendances as $attendance) {
            fputcsv($handle, [
                $attendance->employee->employee_id ?? 'N/A',
                $attendance->employee->user->name ?? 'N/A',
                $attendance->employee->department->name ?? 'N/A',
                $attendance->check_in ? $attendance->check_in->format('Y-m-d') : ($attendance->date ?? 'N/A'),
                $attendance->check_in ? $attendance->check_in->format('Y-m-d H:i:s') : 'N/A',
                $attendance->check_out ? $attendance->check_out->format('Y-m-d H:i:s') : 'N/A',
                ucfirst($attendance->status),
                $attendance->work_hours ? number_format($attendance->work_hours, 2) : '0.00',
                ucfirst($attendance->check_in_method ?? 'manual'),
                ucfirst($attendance->check_out_method ?? 'N/A'),
                $attendance->notes ?? ''
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Show attendance summary.
     */
    public function summary(Request $request)
    {
        $user = Auth::user();
        
        // Only admin and manager can view summary
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Unauthorized to view attendance summary.');
        }
        
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
        
        $query = Attendance::whereBetween('check_in', [$startDate, $endDate]);
        
        // If manager, only show their department
        if ($user->isManager()) {
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('department_id', $user->employee->department_id);
            });
        }
        
        $summary = $query->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = "half-day" THEN 1 ELSE 0 END) as half_day,
                SUM(CASE WHEN status = "overtime" THEN 1 ELSE 0 END) as overtime
            ')
            ->first();

        // Get department-wise summary for admin
        $departmentSummary = [];
        if ($user->isAdmin()) {
            $departmentSummary = \App\Models\Department::withCount(['employees'])
                ->get()
                ->map(function($dept) use ($startDate, $endDate) {
                    $dept->attendance_count = Attendance::whereHas('employee', function($q) use ($dept) {
                            $q->where('department_id', $dept->id);
                        })
                        ->whereBetween('check_in', [$startDate, $endDate])
                        ->count();
                    return $dept;
                });
        }

        return view('attendances.summary', compact('summary', 'startDate', 'endDate', 'departmentSummary'));
    }

    /**
     * Show QR scan page
     */
    public function qrScan()
    {
        return view('attendances.qr-scan');
    }

    /**
     * Process QR code check-in/check-out - SIMPLIFIED VERSION
     */
    public function qrCheckIn(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'qr_data' => 'required|string'
            ]);

            $user = Auth::user();
            
            // Check if user has employee record
            if (!$user->employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not registered as an employee. Please contact administrator.'
                ]);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact administrator.'
                ]);
            }

            // Find QR code
            $qrCode = QrCode::where('token', $request->qr_data)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();
            
            if (!$qrCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired QR code.'
                ]);
            }

            $today = Carbon::today();
            $now = Carbon::now();
            
            // Find today's attendance
            $attendance = Attendance::where('employee_id', $user->employee->id)
                ->whereDate('created_at', $today)
                ->first();

            // No attendance record - Check In
            if (!$attendance) {
                // Determine if late (after 8:15 AM)
                $lateThreshold = Carbon::today()->setHour(8)->setMinute(15);
                $status = $now->gt($lateThreshold) ? 'late' : 'present';

                $attendance = Attendance::create([
                    'employee_id' => $user->employee->id,
                    'check_in' => $now,
                    'check_in_method' => 'qr',
                    'qr_code_id' => $qrCode->id,
                    'status' => $status,
                    'date' => $today->toDateString()
                ]);
                
                $message = $status === 'late' 
                    ? '✓ Check-in successful! (Late arrival)' 
                    : '✓ Check-in successful! Have a great day!';
                
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            // Has check-in but no check-out - Check Out
            if ($attendance && !$attendance->check_out) {
                $attendance->update([
                    'check_out' => $now,
                    'check_out_method' => 'qr'
                ]);
                
                // Calculate hours worked
                $checkIn = Carbon::parse($attendance->check_in);
                $hoursWorked = round($checkIn->diffInMinutes($now) / 60, 1);
                $attendance->update(['work_hours' => $hoursWorked]);
                
                return response()->json([
                    'success' => true,
                    'message' => "✓ Check-out successful! You worked {$hoursWorked} hours today."
                ]);
            }

            // Already checked in and out
            return response()->json([
                'success' => false,
                'message' => 'You have already completed your attendance for today.'
            ]);

        } catch (\Exception $e) {
            Log::error('QR Check-in error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ]);
        }
    }

    /**
     * Calculate work hours for attendance
     */
    private function calculateWorkHours(Attendance $attendance)
    {
        if ($attendance->check_in && $attendance->check_out) {
            $checkIn = Carbon::parse($attendance->check_in);
            $checkOut = Carbon::parse($attendance->check_out);
            $hours = round($checkIn->diffInMinutes($checkOut) / 60, 1);
            $attendance->update(['work_hours' => $hours]);
        }
    }

    /**
     * Get today's attendance status
     */
    public function getTodayStatus()
    {
        try {
            $user = Auth::user();
            
            if (!$user->employee) {
                return response()->json([
                    'status' => 'no_employee'
                ]);
            }

            $attendance = Attendance::where('employee_id', $user->employee->id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if (!$attendance) {
                return response()->json([
                    'status' => 'not_checked_in'
                ]);
            }

            if ($attendance && !$attendance->check_out) {
                return response()->json([
                    'status' => 'checked_in',
                    'check_in_time' => $attendance->check_in->format('H:i:s')
                ]);
            }

            return response()->json([
                'status' => 'completed',
                'check_in_time' => $attendance->check_in->format('H:i:s'),
                'check_out_time' => $attendance->check_out->format('H:i:s'),
                'hours' => $attendance->work_hours
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not fetch status'
            ]);
        }
    }
}