@extends('layouts.master')

@section('title', 'Face Recognition')
@section('page-title', 'Face Recognition Attendance')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Face Recognition</li>
</ul>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Mark Your Attendance</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div id="video-container" class="position-relative d-inline-block">
                        <video id="video" width="640" height="480" autoplay muted playsinline 
                               class="border rounded"></video>
                        <canvas id="canvas" width="640" height="480" 
                                class="position-absolute top-0 start-0"></canvas>
                    </div>
                </div>
                
                <div class="text-center">
                    <button id="start-camera" class="btn btn-primary me-2">
                        <i class="ti ti-camera me-2"></i>Start Camera
                    </button>
                    <button id="recognize-face" class="btn btn-success" disabled>
                        <i class="ti ti-scan-face me-2"></i>Recognize Face
                    </button>
                </div>
                
                <div id="status-message" class="mt-3"></div>
                
                <!-- Attendance Result Card -->
                <div id="attendance-result" class="mt-4" style="display: none;">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 id="result-title"></h4>
                            <p id="result-message" class="mb-0"></p>
                            <p id="result-time" class="text-muted mt-2"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 id="modal-message">Processing face recognition...</h5>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection"></script>
<script>
    let video = document.getElementById('video');
    let canvas = document.getElementById('canvas');
    let context = canvas.getContext('2d');
    let model = null;
    let stream = null;
    let detectionInterval = null;
    
    const startCameraBtn = document.getElementById('start-camera');
    const recognizeBtn = document.getElementById('recognize-face');
    const statusDiv = document.getElementById('status-message');
    const resultDiv = document.getElementById('attendance-result');
    const processingModal = new bootstrap.Modal(document.getElementById('processingModal'));
    
    // Load face detection model
    async function loadModel() {
        try {
            model = await faceLandmarksDetection.load(
                faceLandmarksDetection.SupportedPackages.mediapipeFacemesh
            );
            console.log('Model loaded successfully');
            return true;
        } catch (error) {
            console.error('Error loading model:', error);
            showStatus('Error loading face detection model', 'danger');
            return false;
        }
    }
    
    // Start camera
    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { width: 640, height: 480 } 
            });
            video.srcObject = stream;
            
            startCameraBtn.disabled = true;
            showStatus('Camera started. Loading face detection model...', 'info');
            
            const modelLoaded = await loadModel();
            if (modelLoaded) {
                recognizeBtn.disabled = false;
                showStatus('Camera ready. Look at the camera and click "Recognize Face"', 'success');
                startDetection();
            }
        } catch (error) {
            console.error('Error starting camera:', error);
            showStatus('Error accessing camera. Please ensure camera permissions are granted.', 'danger');
            startCameraBtn.disabled = false;
        }
    }
    
    // Start face detection
    function startDetection() {
        detectionInterval = setInterval(detectFace, 100);
    }
    
    // Detect face in video stream
    async function detectFace() {
        if (!model || !video.srcObject) return;
        
        try {
            const predictions = await model.estimateFaces({
                input: video,
                returnTensors: false,
                flipHorizontal: false
            });
            
            context.clearRect(0, 0, canvas.width, canvas.height);
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            if (predictions.length > 0) {
                predictions.forEach(prediction => {
                    const keypoints = prediction.scaledMesh;
                    
                    // Draw face outline
                    context.beginPath();
                    context.strokeStyle = '#4e73df';
                    context.lineWidth = 3;
                    
                    // Draw jaw outline
                    for (let i = 0; i < 17; i++) {
                        if (i === 0) {
                            context.moveTo(keypoints[i][0], keypoints[i][1]);
                        } else {
                            context.lineTo(keypoints[i][0], keypoints[i][1]);
                        }
                    }
                    context.stroke();
                    
                    // Draw face mesh points
                    context.fillStyle = '#1cc88a';
                    keypoints.forEach(point => {
                        context.beginPath();
                        context.arc(point[0], point[1], 2, 0, 2 * Math.PI);
                        context.fill();
                    });
                });
                
                showStatus('Face detected! You can now recognize.', 'success');
            } else {
                showStatus('No face detected. Please position your face in the camera.', 'warning');
            }
        } catch (error) {
            console.error('Detection error:', error);
        }
    }
    
    // Recognize face for attendance
    async function recognizeFace() {
        try {
            processingModal.show();
            document.getElementById('modal-message').textContent = 'Processing face recognition...';
            
            // Get face descriptor from current frame
            const predictions = await model.estimateFaces({
                input: video,
                returnTensors: false,
                flipHorizontal: false
            });
            
            if (predictions.length === 0) {
                processingModal.hide();
                showStatus('No face detected. Please try again.', 'danger');
                return;
            }
            
            // Convert face landmarks to descriptor
            const faceDescriptor = predictions[0].scaledMesh.flat();
            
            // Capture image from video
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = video.videoWidth;
            tempCanvas.height = video.videoHeight;
            const tempContext = tempCanvas.getContext('2d');
            tempContext.drawImage(video, 0, 0);
            const imageData = tempCanvas.toDataURL('image/jpeg', 0.9);
            
            // Send to server for recognition
            const response = await fetch('{{ route("face.recognize.match") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    face_descriptor: JSON.stringify(faceDescriptor),
                    face_image: imageData
                })
            });
            
            const result = await response.json();
            
            processingModal.hide();
            
            if (result.success) {
                // Show success result
                resultDiv.style.display = 'block';
                document.getElementById('result-title').innerHTML = `
                    <i class="ti ti-circle-check text-success me-2"></i>
                    Attendance Marked Successfully!
                `;
                
                let message = '';
                if (result.attendance.type === 'check_in') {
                    message = `Welcome, ${result.employee.name}! You've checked in at ${result.attendance.time}`;
                } else if (result.attendance.type === 'check_out') {
                    message = `Goodbye, ${result.employee.name}! You've checked out at ${result.attendance.time}`;
                } else {
                    message = result.attendance.message;
                }
                
                document.getElementById('result-message').innerHTML = message;
                document.getElementById('result-time').innerHTML = `
                    <small>Confidence: ${Math.round(result.employee.similarity * 100)}%</small>
                `;
                
                showStatus('Face recognized successfully!', 'success');
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    resultDiv.style.display = 'none';
                }, 5000);
            } else {
                showStatus('Face not recognized. Please try again or register your face.', 'danger');
            }
            
        } catch (error) {
            processingModal.hide();
            console.error('Recognition error:', error);
            showStatus('Error during recognition. Please try again.', 'danger');
        }
    }
    
    // Show status message
    function showStatus(message, type) {
        statusDiv.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }
    
    // Event listeners
    startCameraBtn.addEventListener('click', startCamera);
    recognizeBtn.addEventListener('click', recognizeFace);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (detectionInterval) {
            clearInterval(detectionInterval);
        }
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
</script>
@endpush