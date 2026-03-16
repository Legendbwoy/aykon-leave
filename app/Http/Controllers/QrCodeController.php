<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;

class QrCodeController extends Controller
{
    /**
     * Display the current QR code
     */
    public function index()
    {
        $qrCode = QrCode::latest()->first();
        $qrCodeSvg = null;

        if ($qrCode) {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );

            $writer = new \BaconQrCode\Writer($renderer);
            $qrCodeSvg = $writer->writeString($qrCode->token);
        }

        return view('qr-code.index', compact('qrCode', 'qrCodeSvg'));
    }

    /**
     * Generate a new QR code
     */
    public function regenerate(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is admin
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can regenerate QR codes.'
            ], 403);
        }

        // Generate unique token
        $token = bin2hex(random_bytes(20));
        
        // Clear old QR codes (optional - keep last 10)
        QrCode::where('created_at', '<', now()->subDays(7))->delete();

        $qrCode = QrCode::create([
            'token' => $token,
            'generated_by' => $user->id,
            'expires_at' => now()->addDay(), // 24 hour expiry
        ]);

        // Clear cache
        Cache::forget('current_qr_code');

        return redirect()->route('qr-code.index')
            ->with('success', 'QR code regenerated successfully. The new code is valid for 24 hours.');
    }

    /**
     * Export QR code as PDF
     */
    public function exportPdf()
    {
        $user = Auth::user();
        
        // Check if user is admin
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can export QR codes.'
            ], 403);
        }

        $qrCode = QrCode::latest()->first();
        
        if (!$qrCode) {
            return redirect()->route('qr-code.index')
                ->with('error', 'No QR code found. Please generate one first.');
        }

        // Generate QR code SVG
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qrSvg = $writer->writeString($qrCode->token);

        // Generate PDF
        $pdf = Pdf::loadView('qr-code.pdf', [
            'qrCode' => $qrCode,
            'qrSvg' => $qrSvg,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'expiresAt' => $qrCode->expires_at ? $qrCode->expires_at->format('Y-m-d H:i:s') : 'Never'
        ]);

        return $pdf->download('attendance-qr-code-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * API endpoint to get current valid QR code (for mobile apps)
     */
    public function getCurrentQrCode()
    {
        // Cache the result for 5 minutes to reduce database queries
        $qrCode = Cache::remember('current_qr_code', 300, function() {
            return QrCode::where('expires_at', '>', now())
                ->orWhereNull('expires_at')
                ->latest()
                ->first();
        });

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'No active QR code found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $qrCode->token,
                'generated_at' => $qrCode->created_at->format('Y-m-d H:i:s'),
                'expires_at' => $qrCode->expires_at ? $qrCode->expires_at->format('Y-m-d H:i:s') : null,
                'is_valid' => $qrCode->expires_at ? $qrCode->expires_at->gt(now()) : true
            ]
        ]);
    }

    /**
     * Validate a QR code token (API endpoint)
     */
    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $qrCode = QrCode::where('token', $request->token)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$qrCode) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired QR code'
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Valid QR code',
            'expires_at' => $qrCode->expires_at ? $qrCode->expires_at->format('Y-m-d H:i:s') : null
        ]);
    }

    /**
     * Get QR code history (admin only)
     */
    public function history()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $qrCodes = QrCode::with('generator')
            ->latest()
            ->paginate(20);

        return view('qr-code.history', compact('qrCodes'));
    }

    /**
     * Delete expired QR codes (admin only)
     */
    public function cleanup()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $deleted = QrCode::where('expires_at', '<', now()->subDays(7))->delete();

        return response()->json([
            'success' => true,
            'message' => "Cleaned up {$deleted} expired QR codes",
            'deleted_count' => $deleted
        ]);
    }
}