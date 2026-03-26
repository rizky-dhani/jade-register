<?php

namespace App\Services;

use App\Models\Visitor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Milon\Barcode\DNS2D;

class VisitorQrTokenService
{
    public function generate(Visitor $visitor): void
    {
        do {
            $token = Str::random(64);
        } while (Visitor::where('qr_token', $token)->exists());

        // Generate the verify URL that will be encoded in the QR code
        $qrCodeUrl = url('/visitor/verify/'.$token);

        // Generate QR code image and save to public disk
        $barcodePath = $this->generateQrCodeImage($visitor, $qrCodeUrl);

        $visitor->update([
            'qr_token' => $token,
            'barcode' => $barcodePath,
        ]);
    }

    protected function generateQrCodeImage(Visitor $visitor, string $qrCodeUrl): string
    {
        $directory = 'qr/visitors';
        $filename = 'visitor-'.$visitor->id.'-'.Str::random(8).'.png';
        $fullPath = $directory.'/'.$filename;

        // Ensure directory exists
        Storage::disk('public')->makeDirectory($directory);

        // Generate QR code using milon/barcode
        $dns2d = new DNS2D;
        $qrCodeData = $dns2d->getBarcodePNG($qrCodeUrl, 'QRCODE,H', 6, 6);

        // Decode base64 and save to public disk
        $imageData = base64_decode($qrCodeData);
        Storage::disk('public')->put($fullPath, $imageData);

        return $fullPath;
    }

    public function validate(string $token): ?Visitor
    {
        $visitor = Visitor::where('qr_token', $token)->first();

        if (! $visitor) {
            return null;
        }

        return $visitor;
    }

    public function getQrUrl(Visitor $visitor): ?string
    {
        if (! $visitor->qr_token) {
            return null;
        }

        return url('/visitor/qr-code/'.$visitor->qr_token);
    }

    public function getVerifyUrl(Visitor $visitor): ?string
    {
        if (! $visitor->qr_token) {
            return null;
        }

        return url('/visitor/verify/'.$visitor->qr_token);
    }

    public function canScan(Visitor $visitor): bool
    {
        return ! $visitor->isScanned();
    }
}
