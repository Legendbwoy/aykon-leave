<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code - {{ $employee->user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .employee-details {
            margin-bottom: 30px;
        }
        .employee-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .employee-details th, .employee-details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .qr-code {
            text-align: center;
            margin: 30px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Employee QR Code</h1>
        <h2>{{ $employee->user->name }}</h2>
    </div>

    <div class="employee-details">
        <h3>Employee Information</h3>
        <table>
            <tr>
                <th>Name</th>
                <td>{{ $employee->user->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $employee->user->email }}</td>
            </tr>
            <tr>
                <th>Employee ID</th>
                <td>{{ $employee->employee_id }}</td>
            </tr>
            <tr>
                <th>Department</th>
                <td>{{ $employee->department->name }}</td>
            </tr>
            <tr>
                <th>Position</th>
                <td>{{ $employee->position }}</td>
            </tr>
        </table>
    </div>

    <div class="qr-code">
        <h3>QR Code for Attendance</h3>
        {!! $qrCode !!}
        <p>Scan this QR code to mark attendance</p>
    </div>

    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>Attendance Management System</p>
    </div>
</body>
</html>