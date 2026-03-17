<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get date range from request or default to current month
        $startDate = request('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = request('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        // Get department filter
        $departmentId = request('department_id');
        
        // Get summary statistics
        $summary = $this->getSummaryStats($startDate, $endDate, $departmentId);
        
        // Get departments for filter dropdown
        $departments = Department::all();
        
        // Get recent attendance records
        $recentAttendances = $this->getRecentAttendances($startDate, $endDate, $departmentId);
        
        return view('reports.index', compact('summary', 'departments', 'recentAttendances', 'startDate', 'endDate', 'departmentId'));
    }

    /**
     * API endpoint for summary statistics
     */
    public function apiSummary(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $departmentId = $request->get('department_id');
        
        return response()->json($this->getSummaryStats($startDate, $endDate, $departmentId));
    }

    /**
     * API endpoint for attendance trends chart
     */
    public function apiAttendanceTrends(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $departmentId = $request->get('department_id');
        
        $query = Attendance::query()
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->whereBetween('attendances.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }
        
        // If not admin/manager, only show own department data
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager()) {
            $query->where('employees.department_id', $user->employee->department_id);
        }
        
        $trends = $query->select(
                DB::raw('DATE(attendances.created_at) as date'),
                DB::raw('COUNT(CASE WHEN attendances.status = "present" THEN 1 END) as present'),
                DB::raw('COUNT(CASE WHEN attendances.status = "late" THEN 1 END) as late'),
                DB::raw('COUNT(CASE WHEN attendances.status = "absent" THEN 1 END) as absent')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $labels = [];
        $presentData = [];
        $lateData = [];
        $absentData = [];
        
        foreach ($trends as $trend) {
            $labels[] = Carbon::parse($trend->date)->format('M d');
            $presentData[] = $trend->present;
            $lateData[] = $trend->late;
            $absentData[] = $trend->absent;
        }
        
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $presentData,
                    'borderColor' => '#28a745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Late',
                    'data' => $lateData,
                    'borderColor' => '#ffc107',
                    'backgroundColor' => 'rgba(255, 193, 7, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Absent',
                    'data' => $absentData,
                    'borderColor' => '#dc3545',
                    'backgroundColor' => 'rgba(220, 53, 69, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ]);
    }

    /**
     * API endpoint for department breakdown chart
     */
    public function apiDepartmentBreakdown(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        $query = Department::withCount(['employees' => function($q) {
            $q->whereHas('user', function($u) {
                $u->where('is_active', true);
            });
        }]);
        
        // If not admin, only show own department
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager()) {
            $query->where('id', $user->employee->department_id);
        }
        
        $departments = $query->get();
        
        $labels = [];
        $data = [];
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69'];
        
        foreach ($departments as $index => $dept) {
            $labels[] = $dept->name;
            
            // Get attendance count for this department in date range
            $attendanceCount = Attendance::whereHas('employee', function($q) use ($dept) {
                    $q->where('department_id', $dept->id);
                })
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->count();
            
            $data[] = $attendanceCount;
        }
        
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                    'hoverBackgroundColor' => array_slice($colors, 0, count($labels)),
                    'borderWidth' => 1
                ]
            ]
        ]);
    }

    /**
     * API endpoint for attendance table data
     */
    public function apiAttendanceTable(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $departmentId = $request->get('department_id');
        $limit = $request->get('limit', 100);
        
        $query = Attendance::with(['employee.user', 'employee.department'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($departmentId) {
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        // Filter by user role
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager()) {
            $query->where('employee_id', $user->employee->id);
        } elseif ($user->isManager()) {
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('department_id', $user->employee->department_id);
            });
        }
        
        $attendances = $query->latest('created_at')->limit($limit)->get();
        
        $rows = [];
        foreach ($attendances as $attendance) {
            $rows[] = [
                'date' => $attendance->created_at->format('Y-m-d'),
                'employee' => $attendance->employee->user->name ?? 'N/A',
                'department' => $attendance->employee->department->name ?? 'N/A',
                'status' => ucfirst($attendance->status),
                'status_badge' => $this->getStatusBadge($attendance->status),
                'check_in' => $attendance->check_in ? $attendance->check_in->format('H:i:s') : '---',
                'check_out' => $attendance->check_out ? $attendance->check_out->format('H:i:s') : '---',
                'work_hours' => $attendance->work_hours ? number_format($attendance->work_hours, 2) . ' hrs' : '---'
            ];
        }
        
        return response()->json($rows);
    }

    /**
     * Display attendance report view
     */
    public function attendance(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $departmentId = $request->get('department_id');
        
        $query = Attendance::with(['employee.user', 'employee.department']);
        
        // Apply filters
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        
        if ($departmentId) {
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        // Role-based filtering
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager()) {
            $query->where('employee_id', $user->employee->id);
        } elseif ($user->isManager()) {
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('department_id', $user->employee->department_id);
            });
        }
        
        $attendances = $query->latest('created_at')->paginate(50);
        $departments = Department::all();
        
        return view('reports.attendance', compact('attendances', 'departments', 'startDate', 'endDate', 'departmentId'));
    }

    /**
     * Export attendance report to CSV
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $departmentId = $request->get('department_id');
        
        $query = Attendance::with(['employee.user', 'employee.department'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($departmentId) {
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        // Role-based filtering
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager()) {
            $query->where('employee_id', $user->employee->id);
        } elseif ($user->isManager()) {
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('department_id', $user->employee->department_id);
            });
        }
        
        $attendances = $query->get();
        
        // Generate CSV
        $filename = 'attendance_report_' . Carbon::now()->format('Y_m_d_H_i_s') . '.csv';
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Add headers
        fputcsv($handle, [
            'Date',
            'Employee ID',
            'Employee Name',
            'Department',
            'Status',
            'Check In Time',
            'Check Out Time',
            'Work Hours',
            'Check In Method',
            'Check Out Method'
        ]);
        
        // Add data
        foreach ($attendances as $attendance) {
            fputcsv($handle, [
                $attendance->created_at->format('Y-m-d'),
                $attendance->employee->employee_id ?? 'N/A',
                $attendance->employee->user->name ?? 'N/A',
                $attendance->employee->department->name ?? 'N/A',
                ucfirst($attendance->status),
                $attendance->check_in ? $attendance->check_in->format('H:i:s') : 'N/A',
                $attendance->check_out ? $attendance->check_out->format('H:i:s') : 'N/A',
                $attendance->work_hours ? number_format($attendance->work_hours, 2) : '0.00',
                ucfirst($attendance->check_in_method ?? 'manual'),
                ucfirst($attendance->check_out_method ?? 'N/A')
            ]);
        }
        
        fclose($handle);
        exit;
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStats($startDate, $endDate, $departmentId = null)
    {
        $user = Auth::user();
        
        // Total Employees
        $employeeQuery = Employee::query();
        if ($departmentId) {
            $employeeQuery->where('department_id', $departmentId);
        }
        if (!$user->isAdmin() && !$user->isManager()) {
            $employeeQuery->where('id', $user->employee->id);
        } elseif ($user->isManager()) {
            $employeeQuery->where('department_id', $user->employee->department_id);
        }
        $totalEmployees = $employeeQuery->count();
        
        // Total Attendances in date range
        $attendanceQuery = Attendance::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($departmentId) {
            $attendanceQuery->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        if (!$user->isAdmin() && !$user->isManager()) {
            $attendanceQuery->where('employee_id', $user->employee->id);
        } elseif ($user->isManager()) {
            $attendanceQuery->whereHas('employee', function($q) use ($user) {
                $q->where('department_id', $user->employee->department_id);
            });
        }
        
        $totalAttendances = $attendanceQuery->count();
        
        // Present Today
        $today = Carbon::today();
        $presentToday = Attendance::whereDate('created_at', $today)
            ->whereIn('status', ['present', 'late'])
            ->when($departmentId, function($q) use ($departmentId) {
                $q->whereHas('employee', function($eq) use ($departmentId) {
                    $eq->where('department_id', $departmentId);
                });
            })
            ->when(!$user->isAdmin() && !$user->isManager(), function($q) use ($user) {
                $q->where('employee_id', $user->employee->id);
            })
            ->when($user->isManager(), function($q) use ($user) {
                $q->whereHas('employee', function($eq) use ($user) {
                    $eq->where('department_id', $user->employee->department_id);
                });
            })
            ->count();
        
        // Absent Today (Employees without attendance today)
        $absentToday = 0;
        if ($user->isAdmin() || $user->isManager()) {
            $employeeIds = Employee::when($departmentId, function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                })
                ->when($user->isManager(), function($q) use ($user) {
                    $q->where('department_id', $user->employee->department_id);
                })
                ->pluck('id');
            
            $presentIds = Attendance::whereDate('created_at', $today)
                ->whereIn('employee_id', $employeeIds)
                ->pluck('employee_id');
            
            $absentToday = count($employeeIds) - count($presentIds);
        }
        
        return [
            'totalEmployees' => $totalEmployees,
            'totalAttendances' => $totalAttendances,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday
        ];
    }

    /**
     * Get recent attendances for the table
     */
    private function getRecentAttendances($startDate, $endDate, $departmentId = null)
    {
        $user = Auth::user();
        
        $query = Attendance::with(['employee.user', 'employee.department'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($departmentId) {
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        if (!$user->isAdmin() && !$user->isManager()) {
            $query->where('employee_id', $user->employee->id);
        } elseif ($user->isManager()) {
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('department_id', $user->employee->department_id);
            });
        }
        
        return $query->latest('created_at')->limit(50)->get();
    }

    /**
     * Get status badge class
     */
    private function getStatusBadge($status)
    {
        $classes = [
            'present' => 'success',
            'late' => 'warning',
            'absent' => 'danger',
            'half-day' => 'info',
            'overtime' => 'primary'
        ];
        
        return $classes[$status] ?? 'secondary';
    }
}