<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FaceDescriptor;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FaceRecognitionController extends Controller
{
    public function registerForm()
    {
        $employee = auth()->user()->employee;
        
        if ($employee->face_registered) {
            return redirect()->route('dashboard')
                ->with('info', 'Face already registered.');
        }

        return view('face.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required|json',
            'face_image' => 'required|string',
        ]);

        $employee = auth()->user()->employee;
        
        if ($employee->face_registered) {
            return response()->json([
                'success' => false,
                'message' => 'Face already registered'
            ], 400);
        }

        try {
            // Decode base64 image
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->face_image));
            
            // Generate unique filename
            $filename = 'face_' . $employee->employee_id . '_' . time() . '.jpg';
            $path = 'face-images/' . $filename;
            
            // Store image
            Storage::disk('public')->put($path, $imageData);

            // Save face descriptor
            FaceDescriptor::create([
                'employee_id' => $employee->id,
                'descriptor_data' => $request->face_descriptor,
                'image_path' => $path,
                'confidence_score' => 0.95, // Default confidence
            ]);

            // Update employee face_registered status
            $employee->update(['face_registered' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Face registered successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Face registration failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function recognizeForm()
    {
        return view('face.recognize');
    }

    public function recognize(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required|json',
            'face_image' => 'required|string',
        ]);

        try {
            $inputDescriptor = json_decode($request->face_descriptor, true);
            
            // Get all face descriptors
            $faceDescriptors = FaceDescriptor::with('employee.user')
                ->whereHas('employee.user', function($query) {
                    $query->where('is_active', true);
                })
                ->get();

            $bestMatch = null;
            $highestSimilarity = 0;
            $threshold = 0.6; // Similarity threshold

            foreach ($faceDescriptors as $faceDescriptor) {
                $storedDescriptor = json_decode($faceDescriptor->descriptor_data, true);
                $similarity = $this->calculateSimilarity($inputDescriptor, $storedDescriptor);
                
                if ($similarity > $highestSimilarity) {
                    $highestSimilarity = $similarity;
                    $bestMatch = $faceDescriptor;
                }
            }

            if ($highestSimilarity >= $threshold && $bestMatch) {
                // Process attendance
                $attendance = $this->processAttendance($bestMatch->employee, $request->face_image, $highestSimilarity);

                return response()->json([
                    'success' => true,
                    'message' => 'Face recognized successfully',
                    'employee' => [
                        'name' => $bestMatch->employee->user->name,
                        'employee_id' => $bestMatch->employee->employee_id,
                        'similarity' => $highestSimilarity,
                    ],
                    'attendance' => $attendance,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No matching face found',
                    'similarity' => $highestSimilarity
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Face recognition failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Recognition failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateSimilarity($descriptor1, $descriptor2)
    {
        if (count($descriptor1) !== count($descriptor2)) {
            return 0;
        }

        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        for ($i = 0; $i < count($descriptor1); $i++) {
            $dotProduct += $descriptor1[$i] * $descriptor2[$i];
            $norm1 += $descriptor1[$i] * $descriptor1[$i];
            $norm2 += $descriptor2[$i] * $descriptor2[$i];
        }

        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);

        if ($norm1 == 0 || $norm2 == 0) {
            return 0;
        }

        return $dotProduct / ($norm1 * $norm2);
    }

    private function processAttendance($employee, $faceImage, $confidence)
    {
        // Decode and save check-in/out photo
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $faceImage));
        $filename = 'attendance_' . $employee->employee_id . '_' . time() . '.jpg';
        $path = 'attendance-photos/' . $filename;
        Storage::disk('public')->put($path, $imageData);

        // Check if employee already checked in today
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('check_in', Carbon::today())
            ->first();

        if (!$todayAttendance) {
            // Check in
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'check_in' => Carbon::now(),
                'check_in_method' => 'face',
                'check_in_photo' => $path,
                'check_in_confidence' => $confidence,
                'status' => 'present',
            ]);

            $attendance->determineStatus();

            return [
                'type' => 'check_in',
                'time' => $attendance->check_in->format('H:i:s'),
                'attendance' => $attendance,
            ];
        } elseif (!$todayAttendance->check_out) {
            // Check out
            $todayAttendance->update([
                'check_out' => Carbon::now(),
                'check_out_method' => 'face',
                'check_out_photo' => $path,
                'check_out_confidence' => $confidence,
            ]);

            $todayAttendance->calculateWorkHours();
            $todayAttendance->determineStatus();

            return [
                'type' => 'check_out',
                'time' => $todayAttendance->check_out->format('H:i:s'),
                'attendance' => $todayAttendance,
            ];
        } else {
            return [
                'type' => 'already_completed',
                'message' => 'Already checked in and out for today',
            ];
        }
    }
}