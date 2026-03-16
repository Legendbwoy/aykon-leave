@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Analytics & Reports</h1>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Employees</h5>
                    <h2 id="totalEmployees">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Attendances</h5>
                    <h2 id="totalAttendances">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Present Today</h5>
                    <h2 id="presentToday">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Absent Today</h5>
                    <h2 id="absentToday">0</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Attendance Trends</span>
                    <button class="btn btn-outline-primary btn-sm" id="exportAttendanceChart">Export Chart</button>
                </div>
                <div class="card-body">
                    <canvas id="attendanceTrendsChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Department Breakdown</span>
                    <button class="btn btn-outline-primary btn-sm" id="exportDeptChart">Export Chart</button>
                </div>
                <div class="card-body">
                    <canvas id="departmentBreakdownChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Detailed Attendance Table</span>
                    <a href="{{ route('reports.export') }}" class="btn btn-outline-success btn-sm">Export CSV</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="attendanceTable">
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
                                <!-- Data will be loaded via AJAX -->
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
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- FileSaver.js for exporting charts -->
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script>
    // Example AJAX data fetch (replace with real endpoints)
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch summary stats
        fetch('/api/report/summary')
            .then(res => res.json())
            .then(data => {
                document.getElementById('totalEmployees').textContent = data.totalEmployees;
                document.getElementById('totalAttendances').textContent = data.totalAttendances;
                document.getElementById('presentToday').textContent = data.presentToday;
                document.getElementById('absentToday').textContent = data.absentToday;
            });

        // Attendance Trends Chart
        let attendanceTrendsChart;
        fetch('/api/report/attendance-trends')
            .then(res => res.json())
            .then(data => {
                const ctx = document.getElementById('attendanceTrendsChart').getContext('2d');
                attendanceTrendsChart = new Chart(ctx, {
                    type: 'line',
                    data: data,
                    options: { responsive: true, plugins: { legend: { display: true } } }
                });
            });
        document.getElementById('exportAttendanceChart').onclick = function() {
            attendanceTrendsChart && attendanceTrendsChart.toBase64Image && saveAs(attendanceTrendsChart.toBase64Image(), 'attendance-trends.png');
        };

        // Department Breakdown Chart
        let departmentBreakdownChart;
        fetch('/api/report/department-breakdown')
            .then(res => res.json())
            .then(data => {
                const ctx = document.getElementById('departmentBreakdownChart').getContext('2d');
                departmentBreakdownChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: data,
                    options: { responsive: true, plugins: { legend: { display: true } } }
                });
            });
        document.getElementById('exportDeptChart').onclick = function() {
            departmentBreakdownChart && departmentBreakdownChart.toBase64Image && saveAs(departmentBreakdownChart.toBase64Image(), 'department-breakdown.png');
        };

        // Attendance Table
        fetch('/api/report/attendance-table')
            .then(res => res.json())
            .then(rows => {
                const tbody = document.querySelector('#attendanceTable tbody');
                tbody.innerHTML = '';
                rows.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${row.date}</td><td>${row.employee}</td><td>${row.department}</td><td>${row.status}</td><td>${row.check_in}</td><td>${row.check_out}</td><td>${row.work_hours}</td>`;
                    tbody.appendChild(tr);
                });
            });
    });
</script>
@endpush
