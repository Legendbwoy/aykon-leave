@extends('layouts.master')

@section('title', 'Analytics & Reports')
@section('page-title', 'Analytics & Reports')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Reports</li>
</ul>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate ?? now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate ?? now()->format('Y-m-d') }}">
                        </div>
                        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ ($departmentId ?? '') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ti ti-filter"></i> Apply Filters
                            </button>
                            <button type="button" id="resetFilters" class="btn btn-secondary">
                                <i class="ti ti-refresh"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="avtar avtar-s bg-light-primary rounded-circle mb-2 mx-auto">
                        <i class="ti ti-users text-primary"></i>
                    </div>
                    <h5 class="card-title text-muted">Total Employees</h5>
                    <h2 id="totalEmployees" class="mb-0">{{ $summary['totalEmployees'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="avtar avtar-s bg-light-success rounded-circle mb-2 mx-auto">
                        <i class="ti ti-calendar-check text-success"></i>
                    </div>
                    <h5 class="card-title text-muted">Total Attendances</h5>
                    <h2 id="totalAttendances" class="mb-0">{{ $summary['totalAttendances'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="avtar avtar-s bg-light-info rounded-circle mb-2 mx-auto">
                        <i class="ti ti-clock text-info"></i>
                    </div>
                    <h5 class="card-title text-muted">Present Today</h5>
                    <h2 id="presentToday" class="mb-0">{{ $summary['presentToday'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="avtar avtar-s bg-light-danger rounded-circle mb-2 mx-auto">
                        <i class="ti ti-calendar-off text-danger"></i>
                    </div>
                    <h5 class="card-title text-muted">Absent Today</h5>
                    <h2 id="absentToday" class="mb-0">{{ $summary['absentToday'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attendance Trends</h5>
                    <div>
                        <button class="btn btn-outline-primary btn-sm" id="exportAttendanceChart">
                            <i class="ti ti-download"></i> Export Chart
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="attendanceTrendsChart" height="120"></canvas>
                    <div id="trendsNoData" class="text-center py-4" style="display: none;">
                        <i class="ti ti-chart-line f-40 text-muted"></i>
                        <p class="mb-0 text-muted">No data available for selected period</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Department Breakdown</h5>
                    <button class="btn btn-outline-primary btn-sm" id="exportDeptChart">
                        <i class="ti ti-download"></i> Export
                    </button>
                </div>
                <div class="card-body">
                    <canvas id="departmentBreakdownChart" height="220"></canvas>
                    <div id="deptNoData" class="text-center py-4" style="display: none;">
                        <i class="ti ti-pie-chart f-40 text-muted"></i>
                        <p class="mb-0 text-muted">No data available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Attendance Table -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detailed Attendance Records</h5>
                    <a href="{{ route('reports.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-success btn-sm">
                        <i class="ti ti-file-export"></i> Export CSV
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Work Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendances ?? [] as $attendance)
                                <tr>
                                    <td>{{ $attendance->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $attendance->employee->user->name ?? 'N/A' }}</td>
                                    <td>{{ $attendance->employee->department->name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $statusClass = [
                                                'present' => 'success',
                                                'late' => 'warning',
                                                'absent' => 'danger',
                                                'half-day' => 'info',
                                                'overtime' => 'primary'
                                            ][$attendance->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-light-{{ $statusClass }} px-3 py-2">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i:s') : '---' }}</td>
                                    <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i:s') : '---' }}</td>
                                    <td>{{ $attendance->work_hours ? number_format($attendance->work_hours, 2) . ' hrs' : '---' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="ti ti-inbox f-40 text-muted"></i>
                                        <p class="mb-0 text-muted">No attendance records found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let attendanceTrendsChart = null;
    let departmentBreakdownChart = null;

    // Initialize charts with data from controller
    initializeCharts();

    // Handle filter form submission
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateReportData();
    });

    // Handle reset button
    document.getElementById('resetFilters').addEventListener('click', function() {
        window.location.href = '{{ route("reports.index") }}';
    });

    function initializeCharts() {
        // Fetch trends data
        fetchTrendsData();
        
        // Fetch department data
        fetchDepartmentData();
    }

    function fetchTrendsData() {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData).toString();
        
        fetch(`/api/report/attendance-trends?${params}`)
            .then(res => res.json())
            .then(data => {
                if (data.labels && data.labels.length > 0) {
                    document.getElementById('trendsNoData').style.display = 'none';
                    const ctx = document.getElementById('attendanceTrendsChart').getContext('2d');
                    
                    if (attendanceTrendsChart) {
                        attendanceTrendsChart.destroy();
                    }
                    
                    attendanceTrendsChart = new Chart(ctx, {
                        type: 'line',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                } else {
                    document.getElementById('trendsNoData').style.display = 'block';
                }
            });
    }

    function fetchDepartmentData() {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData).toString();
        
        fetch(`/api/report/department-breakdown?${params}`)
            .then(res => res.json())
            .then(data => {
                if (data.labels && data.labels.length > 0) {
                    document.getElementById('deptNoData').style.display = 'none';
                    const ctx = document.getElementById('departmentBreakdownChart').getContext('2d');
                    
                    if (departmentBreakdownChart) {
                        departmentBreakdownChart.destroy();
                    }
                    
                    departmentBreakdownChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom'
                                }
                            },
                            cutout: '60%'
                        }
                    });
                } else {
                    document.getElementById('deptNoData').style.display = 'block';
                }
            });
    }

    function updateReportData() {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData).toString();
        
        // Update summary cards
        fetch(`/api/report/summary?${params}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('totalEmployees').textContent = data.totalEmployees;
                document.getElementById('totalAttendances').textContent = data.totalAttendances;
                document.getElementById('presentToday').textContent = data.presentToday;
                document.getElementById('absentToday').textContent = data.absentToday;
            });
        
        // Update charts
        fetchTrendsData();
        fetchDepartmentData();
        
        // Update table
        fetch(`/api/report/attendance-table?${params}`)
            .then(res => res.json())
            .then(rows => {
                const tbody = document.querySelector('#attendanceTable tbody');
                tbody.innerHTML = '';
                
                if (rows.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="ti ti-inbox f-40 text-muted"></i>
                                <p class="mb-0 text-muted">No attendance records found</p>
                            </td>
                        </tr>
                    `;
                } else {
                    rows.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${row.date}</td>
                            <td>${row.employee}</td>
                            <td>${row.department}</td>
                            <td><span class="badge bg-light-${row.status_badge} px-3 py-2">${row.status}</span></td>
                            <td>${row.check_in}</td>
                            <td>${row.check_out}</td>
                            <td>${row.work_hours}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            });
        
        // Update export link
        const exportLink = document.querySelector('a[href*="reports.export"]');
        if (exportLink) {
            exportLink.href = `{{ route('reports.export') }}?${params}`;
        }
    }

    // Export chart as image
    document.getElementById('exportAttendanceChart').onclick = function() {
        if (attendanceTrendsChart) {
            const canvas = document.getElementById('attendanceTrendsChart');
            canvas.toBlob(function(blob) {
                saveAs(blob, 'attendance-trends.png');
            });
        } else {
            alert('No chart data to export');
        }
    };

    document.getElementById('exportDeptChart').onclick = function() {
        if (departmentBreakdownChart) {
            const canvas = document.getElementById('departmentBreakdownChart');
            canvas.toBlob(function(blob) {
                saveAs(blob, 'department-breakdown.png');
            });
        } else {
            alert('No chart data to export');
        }
    };
});
</script>
@endpush

@push('styles')
<style>
.avtar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avtar-s {
    width: 40px;
    height: 40px;
}
.avtar i {
    font-size: 1.25rem;
}
.card {
    transition: transform 0.2s;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.table th {
    font-weight: 600;
    color: #495057;
}
.badge {
    font-weight: 500;
    padding: 0.5rem 1rem;
}
</style>
@endpush