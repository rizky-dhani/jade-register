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

        if (! $registration->payment_proof) {
            abort(404, 'Payment proof not found.');
        }

        $path = $registration->payment_proof;

        if (! Storage::disk('payment-proofs')->exists($path)) {
            abort(404, 'Payment proof file not found.');
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

        if (! $registration->payment_proof) {
            abort(404, 'Payment proof not found.');
        }

        $path = $registration->payment_proof;

        if (! Storage::disk('payment-proofs')->exists($path)) {
            abort(404, 'Payment proof file not found.');
        }

        $mimeType = Storage::disk('payment-proofs')->mimeType($path);

        return response()->file(
            Storage::disk('payment-proofs')->path($path),
            ['Content-Type' => $mimeType]
        );
    }
}
