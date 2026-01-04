<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Manufacturer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EquipmentImportExportService
{
    private array $importErrors = [];
    private int $importedCount = 0;
    private int $skippedCount = 0;

    public function importFromCsv(UploadedFile $file, array $fieldMapping, ?int $userId = null): array
    {
        $this->importErrors = [];
        $this->importedCount = 0;
        $this->skippedCount = 0;

        $rows = $this->parseCsv($file);
        $headers = array_shift($rows);

        if (empty($rows)) {
            throw new \RuntimeException('CSV file is empty or contains only headers.');
        }

        DB::beginTransaction();

        try {
            foreach ($rows as $rowIndex => $row) {
                $lineNumber = $rowIndex + 2; // +2 for header row and 0-index

                try {
                    $data = $this->mapRowToData($row, $headers, $fieldMapping);
                    $this->validateImportRow($data, $lineNumber);
                    $this->createEquipmentFromRow($data, $userId);
                    $this->importedCount++;
                } catch (ValidationException $e) {
                    $this->importErrors[] = [
                        'line' => $lineNumber,
                        'errors' => $e->errors(),
                        'row' => array_combine($headers, $row),
                    ];
                    $this->skippedCount++;
                } catch (\Exception $e) {
                    $this->importErrors[] = [
                        'line' => $lineNumber,
                        'errors' => ['general' => [$e->getMessage()]],
                        'row' => array_combine($headers, $row),
                    ];
                    $this->skippedCount++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->importErrors,
            'total_rows' => count($rows),
        ];
    }

    public function exportToCsv(Collection $equipment, array $fields = null): string
    {
        $fields = $fields ?? $this->getDefaultExportFields();
        $output = fopen('php://temp', 'r+');

        // Header row
        fputcsv($output, array_map(fn($f) => $this->fieldToLabel($f), $fields));

        // Data rows
        foreach ($equipment as $item) {
            $row = [];
            foreach ($fields as $field) {
                $row[] = $this->getFieldValue($item, $field);
            }
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    public function getAvailableImportFields(): array
    {
        return [
            'name' => ['label' => 'Equipment Name', 'required' => true],
            'type' => ['label' => 'Type/Category', 'required' => true],
            'manufacturer' => ['label' => 'Manufacturer', 'required' => false],
            'model' => ['label' => 'Model Number', 'required' => false],
            'serial_number' => ['label' => 'Serial Number', 'required' => false],
            'asset_tag' => ['label' => 'Asset Tag', 'required' => false],
            'purchase_date' => ['label' => 'Purchase Date', 'required' => false],
            'purchase_price' => ['label' => 'Purchase Price', 'required' => false],
            'purchase_vendor' => ['label' => 'Vendor', 'required' => false],
            'status' => ['label' => 'Status', 'required' => false],
            'location_name' => ['label' => 'Location Name', 'required' => false],
            'location_address' => ['label' => 'Address', 'required' => false],
            'location_building' => ['label' => 'Building', 'required' => false],
            'location_floor' => ['label' => 'Floor', 'required' => false],
            'location_room' => ['label' => 'Room', 'required' => false],
            'ip_address' => ['label' => 'IP Address', 'required' => false],
            'mac_address' => ['label' => 'MAC Address', 'required' => false],
            'notes' => ['label' => 'Notes', 'required' => false],
        ];
    }

    public function generateSampleCsv(): string
    {
        $fields = array_keys($this->getAvailableImportFields());
        $output = fopen('php://temp', 'r+');

        fputcsv($output, $fields);

        // Sample row
        fputcsv($output, [
            'Server-001',
            'Server',
            'Dell',
            'PowerEdge R750',
            'XYZ123456',
            'ASSET-001',
            '2024-01-15',
            '5000.00',
            'Dell Direct',
            'operational',
            'Main Data Center',
            '123 Tech Street',
            'Building A',
            'Floor 2',
            'Server Room',
            '192.168.1.100',
            'AA:BB:CC:DD:EE:FF',
            'Primary web server',
        ]);

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    // ─── Private Methods ──────────────────────────────────────────────

    private function parseCsv(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function mapRowToData(array $row, array $headers, array $fieldMapping): array
    {
        $data = [];
        $rowData = array_combine($headers, $row);

        foreach ($fieldMapping as $csvColumn => $equipmentField) {
            if (isset($rowData[$csvColumn]) && $equipmentField) {
                $data[$equipmentField] = trim($rowData[$csvColumn]);
            }
        }

        return $data;
    }

    private function validateImportRow(array $data, int $lineNumber): void
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'serial_number' => 'nullable|unique:equipment,serial_number',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:' . implode(',', array_keys(Equipment::statusOptions())),
            'ip_address' => 'nullable|ip',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function createEquipmentFromRow(array $data, ?int $userId): Equipment
    {
        // Handle manufacturer lookup/creation
        if (!empty($data['manufacturer'])) {
            $manufacturer = Manufacturer::firstOrCreate(
                ['name' => $data['manufacturer']],
                ['is_active' => true]
            );
            $data['manufacturer_id'] = $manufacturer->id;
        }

        // Parse date if string
        if (!empty($data['purchase_date']) && is_string($data['purchase_date'])) {
            $data['purchase_date'] = \Carbon\Carbon::parse($data['purchase_date']);
        }

        // Set defaults
        $data['status'] = $data['status'] ?? Equipment::STATUS_OPERATIONAL;
        $data['lifecycle_status'] = Equipment::LIFECYCLE_NEW;

        return Equipment::create($data);
    }

    private function getDefaultExportFields(): array
    {
        return [
            'id',
            'name',
            'type',
            'manufacturer',
            'model',
            'serial_number',
            'asset_tag',
            'purchase_date',
            'purchase_price',
            'status',
            'health_score',
            'lifecycle_status',
            'location_building',
            'location_floor',
            'location_room',
            'ip_address',
            'mac_address',
        ];
    }

    private function getFieldValue(Equipment $equipment, string $field): mixed
    {
        return match ($field) {
            'manufacturer' => $equipment->manufacturer?->name ?? $equipment->manufacturer,
            'category' => $equipment->category?->name,
            'assigned_user' => $equipment->assignedUser?->name,
            'organization' => $equipment->organization?->name,
            'purchase_date' => $equipment->purchase_date?->format('Y-m-d'),
            default => $equipment->{$field} ?? '',
        };
    }

    private function fieldToLabel(string $field): string
    {
        return ucwords(str_replace('_', ' ', $field));
    }
}
