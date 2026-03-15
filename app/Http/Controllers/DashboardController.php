<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isManager()) {
            return $this->managerDashboard();
        } else {
            return $this->employeeDashboard();
        }
    }

    private function adminDashboard()
    {
        $totalEmployees = Employee::count();
        $totalDepartments = Department::count();
        $presentToday = Attendance::whereDate('check_in', Carbon::today())
            ->whereNotNull('check_in')
            ->count();
        $totalAttendances = Attendance::whereDate('check_in', Carbon::today())
            ->whereNotNull('check_in')
            ->count();

        $recentAttendances = Attendance::with(['employee.user', 'employee.department'])
            ->latest('check_in')
            ->take(10)
            ->get();

        $attendanceStats = [
            'present' => Attendance::whereDate('check_in', Carbon::today())
                ->whereNotNull('check_in')
                ->where('status', '!=', 'absent')
                ->count(),
            'late' => Attendance::whereDate('check_in', Carbon::today())
                ->where('status', 'late')
                ->count(),
            'absent' => Employee::count() - Attendance::whereDate('check_in', Carbon::today())
                ->whereNotNull('check_in')
                ->count(),
        ];

        return view('dashboard.admin', compact(
            'totalEmployees',
            'totalDepartments',
            'presentToday',
            'totalAttendances',
            'recentAttendances',
            'attendanceStats'
        ));
    }

    private function managerDashboard()
    {
        $departmentId = Auth::user()->employee->department_id;
        
        $totalEmployees = Employee::where('department_id', $departmentId)->count();
        $presentToday = Attendance::whereHas('employee', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->whereDate('check_in', Carbon::today())
            ->whereNotNull('check_in')
            ->count();

        $recentAttendances = Attendance::whereHas('employee', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->with(['employee.user', 'employee.department'])
            ->latest('check_in')
            ->take(10)
            ->get();

        return view('dashboard.manager', compact(
            'totalEmployees',
            'presentToday',
            'recentAttendances'
        ));
    }

    private function employeeDashboard()
    {
        $employee = Auth::user()->employee;
        
        if (!$employee) {
            return redirect()->route('profile.edit')
                ->with('error', 'Please complete your employee profile first.');
        }
        
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('check_in', Carbon::today())
            ->first();

        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->latest('check_in')
            ->take(10)
            ->get();

        $monthlyStats = [
            'total' => Attendance::where('employee_id', $employee->id)
                ->whereMonth('check_in', Carbon::now()->month)
                ->whereNotNull('check_in')
                ->count(),
            'late' => Attendance::where('employee_id', $employee->id)
                ->whereMonth('check_in', Carbon::now()->month)
                ->where('status', 'late')
                ->count(),
            'absent' => Attendance::where('employee_id', $employee->id)
                ->whereMonth('check_in', Carbon::now()->month)
                ->where('status', 'absent')
                ->count(),
        ];

        return view('dashboard.employee', compact(
            'employee',
            'todayAttendance',
            'recentAttendances',
            'monthlyStats'
        ));
    }
}