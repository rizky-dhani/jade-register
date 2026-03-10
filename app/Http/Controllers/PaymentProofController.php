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

        $path = 'payment-proofs/'.$registration->payment_proof_path;

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'Payment proof file not found.');
        }

        $mimeType = Storage::disk('public')->mimeType($path);
        $filename = 'payment-proof-'.$registration->registration_code.'.'.pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($path, $filename, [
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

        $path = 'payment-proofs/'.$registration->payment_proof_path;

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'Payment proof file not found.');
        }

        $mimeType = Storage::disk('public')->mimeType($path);

        return response()->file(
            Storage::disk('public')->path($path),
            ['Content-Type' => $mimeType]
        );
    }
}
