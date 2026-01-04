<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Support\Str;

class QrCodeService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.url');
    }

    /**
     * Generate a unique QR code identifier for equipment
     */
    public function generateQrCode(Equipment $equipment): string
    {
        if ($equipment->qr_code) {
            return $equipment->qr_code;
        }

        $prefix = 'EQ';
        $uniquePart = strtoupper(Str::random(8));
        $qrCode = sprintf('%s-%06d-%s', $prefix, $equipment->id, $uniquePart);

        $equipment->update(['qr_code' => $qrCode]);

        return $qrCode;
    }

    /**
     * Generate a barcode for equipment
     */
    public function generateBarcode(Equipment $equipment): string
    {
        if ($equipment->barcode) {
            return $equipment->barcode;
        }

        // Generate Code 128 compatible barcode
        $barcode = sprintf(
            '%s%08d',
            'EQ',
            $equipment->id
        );

        $equipment->update(['barcode' => $barcode]);

        return $barcode;
    }

    /**
     * Get the URL that the QR code should link to
     */
    public function getEquipmentUrl(Equipment $equipment): string
    {
        return sprintf('%s/equipment/%d', $this->baseUrl, $equipment->id);
    }

    /**
     * Get the mobile scan URL for quick lookup
     */
    public function getMobileScanUrl(Equipment $equipment): string
    {
        $qrCode = $equipment->qr_code ?? $this->generateQrCode($equipment);

        return sprintf('%s/scan/%s', $this->baseUrl, $qrCode);
    }

    /**
     * Generate QR code data URL (SVG) for embedding in HTML
     * Uses simple QR code generation without external dependencies
     */
    public function generateQrCodeSvg(Equipment $equipment, int $size = 200): string
    {
        $url = $this->getEquipmentUrl($equipment);

        // Use Google Charts API for QR code generation (simple approach)
        // In production, you'd use a package like simplesoftwareio/simple-qrcode
        return sprintf(
            'https://chart.googleapis.com/chart?chs=%dx%d&cht=qr&chl=%s&choe=UTF-8',
            $size,
            $size,
            urlencode($url)
        );
    }

    /**
     * Generate label data for equipment
     */
    public function getLabelData(Equipment $equipment): array
    {
        return [
            'qr_code' => $equipment->qr_code ?? $this->generateQrCode($equipment),
            'barcode' => $equipment->barcode ?? $this->generateBarcode($equipment),
            'qr_url' => $this->generateQrCodeSvg($equipment),
            'equipment_url' => $this->getEquipmentUrl($equipment),
            'name' => $equipment->name,
            'serial_number' => $equipment->serial_number,
            'asset_tag' => $equipment->asset_tag,
            'model' => $equipment->model,
            'manufacturer' => $equipment->manufacturer?->name ?? $equipment->manufacturer,
            'location' => $equipment->location_full,
        ];
    }

    /**
     * Lookup equipment by QR code
     */
    public function findByQrCode(string $qrCode): ?Equipment
    {
        return Equipment::where('qr_code', $qrCode)->first();
    }

    /**
     * Lookup equipment by barcode
     */
    public function findByBarcode(string $barcode): ?Equipment
    {
        return Equipment::where('barcode', $barcode)->first();
    }

    /**
     * Bulk generate QR codes for equipment without them
     */
    public function bulkGenerateQrCodes(?int $limit = 100): int
    {
        $equipment = Equipment::whereNull('qr_code')
            ->limit($limit)
            ->get();

        foreach ($equipment as $item) {
            $this->generateQrCode($item);
        }

        return $equipment->count();
    }
}
