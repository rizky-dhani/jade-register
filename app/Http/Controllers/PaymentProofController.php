<?php

namespace App\Http\Controllers;

use App\Models\SeminarRegistration;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PaymentProofController extends Controller
{
    public function show(SeminarRegistration $registration)
    {
        if (! Gate::allows('view-payment-proof', $registration)) {
            abort(403, 'Unauthorized to view this payment proof.');
        }

        if (! $registration->payment_proof_path) {
            abort(404, 'Payment proof not found.');
        }

        $path = $registration->payment_proof_path;

        // Try new disk location first, then fall back to public disk
        if (! Storage::disk('payment-proofs')->exists($path)) {
            $publicPath = 'payment-proofs/'.$path;
            if (! Storage::disk('public')->exists($publicPath)) {
                abort(404, 'Payment proof file not found.');
            }

            // Serve from public disk (legacy location)
            $mimeType = Storage::disk('public')->mimeType($publicPath);
            $filename = 'payment-proof-'.$registration->registration_code.'.'.pathinfo($path, PATHINFO_EXTENSION);

            return Storage::disk('public')->download($publicPath, $filename, [
                'Content-Type' => $mimeType,
            ]);
        }

        $mimeType = Storage::disk('payment-proofs')->mimeType($path);
        $filename = 'payment-proof-'.$registration->registration_code.'.'.pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk('payment-proofs')->download($path, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function preview(SeminarRegistration $registration)
    {
        if (! Gate::allows('view-payment-proof', $registration)) {
            abort(403, 'Unauthorized to view this payment proof.');
        }

        if (! $registration->payment_proof_path) {
            abort(404, 'Payment proof not found.');
        }

        $path = $registration->payment_proof_path;

        // Try new disk location first, then fall back to public disk
        if (! Storage::disk('payment-proofs')->exists($path)) {
            $publicPath = 'payment-proofs/'.$path;
            if (! Storage::disk('public')->exists($publicPath)) {
                abort(404, 'Payment proof file not found.');
            }

            // Serve from public disk (legacy location)
            $mimeType = Storage::disk('public')->mimeType($publicPath);

            return response()->file(
                Storage::disk('public')->path($publicPath),
                ['Content-Type' => $mimeType]
            );
        }

        $mimeType = Storage::disk('payment-proofs')->mimeType($path);

        return response()->file(
            Storage::disk('payment-proofs')->path($path),
            ['Content-Type' => $mimeType]
        );
    }
}
