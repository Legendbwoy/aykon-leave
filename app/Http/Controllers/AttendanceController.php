<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
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
        // Admin has unrestricted access
        // if (!\Gate::allows('manage-employees')) {
        //     abort(403, 'Unauthorized action.');
        // }
        
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
        // Admin has unrestricted access
        // Check if user can create attendance
        // if (!\Gate::allows('manage-employees')) {
        //     abort(403, 'Unauthorized action.');
        // }

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
            $attendance->calculateWorkHours();
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
        if ($user->isAdmin()) {
            // Admin can view all
        } elseif ($user->isManager()) {
            // Manager can only view their department
            if ($user->employee->department_id !== $attendance->employee->department_id) {
                abort(403, 'Unauthorized to view this attendance record.');
            }
        } else {
            // Employee can only view their own
            if ($user->employee->id !== $attendance->employee_id) {
                abort(403, 'Unauthorized to view this attendance record.');
            }
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
        
        // Admin has unrestricted access
        // Check if user can edit attendance
        // if (!\Gate::allows('manage-employees')) {
        //     abort(403, 'Unauthorized to edit attendance records.');
        // }
        
        // Additional check for managers
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
        
        // Admin has unrestricted access
        // Check if user can update attendance
        // if (!\Gate::allows('manage-employees')) {
        //     abort(403, 'Unauthorized to update attendance records.');
        // }
        
        // Additional check for managers
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
            $attendance->calculateWorkHours();
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(Attendance $attendance)
    {
        // Admin has unrestricted access
        // Only admin can delete attendance records
        // if (!\Gate::allows('manage-users')) {
        //     abort(403, 'Unauthorized to delete attendance records.');
        // }
        
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
        
        // Check if user can view this employee's attendance
        if ($user->isAdmin()) {
            // Admin can view all
        } elseif ($user->isManager()) {
            // Manager can only view their department
            if ($user->employee->department_id !== $employee->department_id) {
                abort(403, 'Unauthorized to view this employee\'s attendance.');
            }
        } else {
            // Employee can only view their own
            if ($user->employee->id !== $employee->id) {
                abort(403, 'Unauthorized to view this employee\'s attendance.');
            }
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
        // Admin has unrestricted access
        // Only admin and manager can export
        // if (!\Gate::allows('manage-employees')) {
        //     abort(403, 'Unauthorized to export attendance records.');
        // }
        
        // Logic for exporting attendance (CSV, Excel, etc.)
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
        
        // Admin has unrestricted access
        // Only admin and manager can view summary
        // if (!\Gate::allows('manage-employees')) {
        //     abort(403, 'Unauthorized to view attendance summary.');
        // }
        
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
     * Mark attendance manually (for testing purposes)
     */
    public function qrCheckIn(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
            'device_time' => 'required|date',
        ]);

        // Validate device time against server time
        $deviceTime = Carbon::parse($request->device_time);
        $serverTime = Carbon::now();
        $timeDifference = abs($deviceTime->diffInMinutes($serverTime));

        if ($timeDifference > 5) {
            return response()->json([
                'success' => false,
                'message' => 'Device time does not match server time. Please update your device time and try again.',
                'time_mismatch' => true,
                'server_time' => $serverTime->format('Y-m-d H:i:s'),
                'device_time' => $deviceTime->format('Y-m-d H:i:s')
            ], 400);
        }

        // Validate QR token
        $qrCode = \App\Models\QrCode::where('token', $request->qr_data)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        if (! $qrCode) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired QR code'], 404);
        }

        $user = Auth::user();
        if (! $user->employee) {
            return response()->json(['success' => false, 'message' => 'No employee record found'], 404);
        }

        $employee = $user->employee;

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('check_in', Carbon::today())
            ->first();

        if (!$todayAttendance) {
            // Check in
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'check_in' => Carbon::now(),
                'check_in_method' => 'qr',
                'status' => 'present',
                'date' => Carbon::today()->toDateString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QR check-in successful',
                'type' => 'check_in',
                'time' => $attendance->check_in->format('H:i:s'),
            ]);
        } elseif (! $todayAttendance->check_out) {
            // Check out
            $todayAttendance->update([
                'check_out' => Carbon::now(),
                'check_out_method' => 'qr',
            ]);
            $todayAttendance->calculateWorkHours();

            return response()->json([
                'success' => true,
                'message' => 'QR check-out successful',
                'type' => 'check_out',
                'time' => $todayAttendance->check_out->format('H:i:s'),
                'hours' => $todayAttendance->work_hours,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Already checked in and out today',
        ]);
    }

    public function qrScan()
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access QR scanning.');
        }

        // Check if user has employee record
        if (!auth()->user()->employee) {
            return redirect()->route('dashboard')->with('error', 'Employee record not found. Please contact administrator.');
        }

        return view('attendances.qr-scan');
    }
}