<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QrCodeController extends Controller
{

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

    public function regenerate(Request $request)
    {
        // Admin has unrestricted access
        // if (!\Gate::allows('manage-employees')) {
        //     abort(403, 'Unauthorized');
        // }

        $user = Auth::user();
        $token = bin2hex(random_bytes(20));

        $qrCode = QrCode::create([
            'token' => $token,
            'generated_by' => $user->id,
            'expires_at' => now()->addDay(),
        ]);

        return redirect()->route('qr-code.index')
            ->with('success', 'QR code regenerated successfully.');
    }

    public function exportPdf()
    {
        // Admin has unrestricted access
        // if (!\Gate::allows('manage-employees')) {
        //     abort(403, 'Unauthorized');
        // }

        $qrCode = QrCode::latest()->first();
        if (! $qrCode) {
            return redirect()->route('qr-code.index')
                ->with('error', 'No QR code found. Please generate one first.');
        }

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qrSvg = $writer->writeString($qrCode->token);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('qr-code.pdf', compact('qrCode', 'qrSvg'));

        return $pdf->download('attendance-qr-code.pdf');
    }
}
