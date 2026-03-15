<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show()
    {
        $user = auth()->user();
        $user->load('employee.department');
        
        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit()
    {
        $user = auth()->user();
        $user->load('employee');
        
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Update user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update or create employee details
        if ($user->employee) {
            $employeeData = [
                'phone' => $request->phone,
                'address' => $request->address,
            ];

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($user->employee->profile_photo) {
                    Storage::disk('public')->delete($user->employee->profile_photo);
                }
                
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $employeeData['profile_photo'] = $path;
            }

            $user->employee->update($employeeData);
        }

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Show the form for changing password.
     */
    public function changePassword()
    {
        return view('profile.change-password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, auth()->user()->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Upload profile photo.
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();
        
        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.'
            ], 404);
        }

        // Delete old photo
        if ($user->employee->profile_photo) {
            Storage::disk('public')->delete($user->employee->profile_photo);
        }

        // Store new photo
        $path = $request->file('profile_photo')->store('profile-photos', 'public');
        $user->employee->update(['profile_photo' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Profile photo uploaded successfully.',
            'photo_url' => asset('storage/' . $path)
        ]);
    }

    /**
     * Remove profile photo.
     */
    public function removePhoto()
    {
        $user = auth()->user();
        
        if ($user->employee && $user->employee->profile_photo) {
            Storage::disk('public')->delete($user->employee->profile_photo);
            $user->employee->update(['profile_photo' => null]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Profile photo removed successfully.');
    }

    /**
     * Get user activity log.
     */
    public function activity()
    {
        $user = auth()->user();
        
        // Get recent attendances
        $attendances = [];
        if ($user->employee) {
            $attendances = $user->employee->attendances()
                ->latest()
                ->take(10)
                ->get();
        }

        return view('profile.activity', compact('user', 'attendances'));
    }

    /**
     * Update notification preferences.
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
        ]);

        $user = auth()->user();
        
        // Save to database using settings
        $settings = $user->settings ?? [];
        $settings['notifications'] = [
            'email' => $request->boolean('email_notifications'),
            'sms' => $request->boolean('sms_notifications')
        ];
        $user->settings = $settings;
        $user->save();

        return redirect()->route('profile.show')
            ->with('success', 'Notification preferences updated.');
    }

    /**
     * Delete user account.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, auth()->user()->password)) {
                    $fail('The password is incorrect.');
                }
            }],
        ]);

        $user = auth()->user();
        
        // Delete employee record if exists
        if ($user->employee) {
            if ($user->employee->profile_photo) {
                Storage::disk('public')->delete($user->employee->profile_photo);
            }
            $user->employee->delete();
        }

        // Delete user
        $user->delete();

        // Logout
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'Your account has been deleted successfully.');
    }
}