@extends('layouts.master')

@section('title', 'Register Face')
@section('page-title', 'Face Registration')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Face Registration</li>
</ul>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Register Your Face for Attendance</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Please ensure good lighting and look directly at the camera.
                </div>
                
                <div class="text-center mb-4">
                    <div id="video-container" class="position-relative d-inline-block">
                        <video id="video" width="640" height="480" autoplay muted playsinline 
                               class="border rounded"></video>
                        <canvas id="canvas" width="640" height="480" 
                                class="position-absolute top-0 start-0" style="display: none;"></canvas>
                    </div>
                </div>
                
                <div class="text-center">
                    <button id="start-camera" class="btn btn-primary me-2">
                        <i class="ti ti-camera me-2"></i>Start Camera
                    </button>
                    <button id="capture-face" class="btn btn-success" disabled>
                        <i class="ti ti-camera-selfie me-2"></i>Capture Face
                    </button>
                </div>
                
                <div id="status-message" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Face Detection Status Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 id="modal-message">Processing face registration...</h5>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #video {
        max-width: 100%;
        height: auto;
    }
</style>
@endpush

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
    const captureBtn = document.getElementById('capture-face');
    const statusDiv = document.getElementById('status-message');
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
            showStatus('Camera started. Please wait for model to load...', 'info');
            
            // Load model after camera starts
            const modelLoaded = await loadModel();
            if (modelLoaded) {
                captureBtn.disabled = false;
                showStatus('Camera ready. Please look at the camera and click "Capture Face"', 'success');
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
            
            if (predictions.length > 0) {
                // Draw bounding box
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
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
                    
                    // Draw eyes and eyebrows
                    context.strokeStyle = '#1cc88a';
                    context.lineWidth = 2;
                    
                    // Right eyebrow
                    context.beginPath();
                    for (let i = 17; i <= 21; i++) {
                        if (i === 17) {
                            context.moveTo(keypoints[i][0], keypoints[i][1]);
                        } else {
                            context.lineTo(keypoints[i][0], keypoints[i][1]);
                        }
                    }
                    context.stroke();
                    
                    // Left eyebrow
                    context.beginPath();
                    for (let i = 22; i <= 26; i++) {
                        if (i === 22) {
                            context.moveTo(keypoints[i][0], keypoints[i][1]);
                        } else {
                            context.lineTo(keypoints[i][0], keypoints[i][1]);
                        }
                    }
                    context.stroke();
                    
                    // Draw face mesh points
                    context.fillStyle = '#f6c23e';
                    keypoints.forEach(point => {
                        context.beginPath();
                        context.arc(point[0], point[1], 2, 0, 2 * Math.PI);
                        context.fill();
                    });
                });
                
                canvas.style.display = 'block';
                showStatus('Face detected! You can now capture.', 'success');
                captureBtn.disabled = false;
            } else {
                canvas.style.display = 'none';
                context.clearRect(0, 0, canvas.width, canvas.height);
                showStatus('No face detected. Please position your face in the camera.', 'warning');
            }
        } catch (error) {
            console.error('Detection error:', error);
        }
    }
    
    // Capture face for registration
    async function captureFace() {
        try {
            processingModal.show();
            document.getElementById('modal-message').textContent = 'Processing face registration...';
            
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
            
            // Send to server
            const response = await fetch('{{ route("face.register.store") }}', {
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
                showStatus('Face registered successfully! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("dashboard") }}';
                }, 2000);
            } else {
                showStatus('Registration failed: ' + result.message, 'danger');
            }
            
        } catch (error) {
            processingModal.hide();
            console.error('Capture error:', error);
            showStatus('Error capturing face. Please try again.', 'danger');
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
    captureBtn.addEventListener('click', captureFace);
    
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