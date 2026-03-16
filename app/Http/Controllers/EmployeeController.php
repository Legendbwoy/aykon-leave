<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('employee_id', 'like', "%{$search}%");
        }

        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->paginate(15);
        $departments = Department::all();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'department_id' => 'required|exists:departments,id',
            'employee_id' => 'nullable|string|unique:employees',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'hire_date' => 'required|date',
            'position' => 'required|string',
            'salary' => 'nullable|numeric',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'force_password_change' => true,
        ]);

        $employeeData = [
            'user_id' => $user->id,
            'department_id' => $request->department_id,
            'employee_id' => $request->employee_id ?? \Str::uuid()->toString(),
            'phone' => $request->phone,
            'address' => $request->address,
            'hire_date' => $request->hire_date,
            'position' => $request->position,
            'salary' => $request->salary,
        ];

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $employeeData['profile_photo'] = $path;
        }

        $employee = Employee::create($employeeData);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'department', 'attendances' => function($query) {
            $query->latest()->limit(30);
        }]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::all();
        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employee->user_id,
            'department_id' => 'required|exists:departments,id',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'position' => 'required|string',
            'salary' => 'nullable|numeric',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        $employee->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $employeeData = [
            'department_id' => $request->department_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'position' => $request->position,
            'salary' => $request->salary,
        ];

        if ($request->hasFile('profile_photo')) {
            if ($employee->profile_photo) {
                Storage::disk('public')->delete($employee->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $employeeData['profile_photo'] = $path;
        }

        $employee->update($employeeData);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->profile_photo) {
            Storage::disk('public')->delete($employee->profile_photo);
        }
        
        $employee->user->delete();
        
        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function toggleStatus(Employee $employee)
    {
        $employee->user->update([
            'is_active' => !$employee->user->is_active
        ]);

        return redirect()->back()
            ->with('success', 'Employee status updated successfully.');
    }

    public function qrCode(Employee $employee)
    {
        // Generate QR code with employee_id using BaconQrCode
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qrCode = $writer->writeString($employee->employee_id);

        return view('employees.qr-code', compact('employee', 'qrCode'));
    }

    public function regenerateQrCode(Employee $employee)
    {
        // QR code regeneration is essentially the same as generation since it's based on employee_id
        // But we can add a timestamp or something if needed
        // For now, just redirect to the qr code view
        return redirect()->route('employees.qr-code', $employee)
            ->with('success', 'QR Code regenerated successfully.');
    }

    public function exportQrCodePdf(Employee $employee)
    {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qrCode = $writer->writeString($employee->employee_id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('employees.qr-code-pdf', compact('employee', 'qrCode'));

        return $pdf->download('qr-code-' . $employee->employee_id . '.pdf');
    }
}