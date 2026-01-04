<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\EquipmentDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Upload and attach a file to a model using the polymorphic Attachment system.
     *
     * @param Model $model The model to attach the file to.
     * @param UploadedFile $file The file to upload.
     * @param string|null $label Optional label for the attachment.
     * @param string|null $kind Optional kind/category (e.g., 'before_photo', 'after_photo', 'signed_work_order').
     * @param array|null $meta Optional metadata.
     * @param string $disk Storage disk to use.
     * @return Attachment
     */
    public function attach(
        Model $model,
        UploadedFile $file,
        ?string $label = null,
        ?string $kind = null,
        ?array $meta = null,
        string $disk = 'public'
    ): Attachment {
        $path = $this->storeFile($file, 'attachments/' . $model->getTable(), $disk);

        return Attachment::create([
            'attachable_type' => get_class($model),
            'attachable_id' => $model->getKey(),
            'uploaded_by_user_id' => auth()->id(),
            'label' => $label ?? $file->getClientOriginalName(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'kind' => $kind,
            'meta' => $meta,
        ]);
    }

    /**
     * Upload and create a specific EquipmentDocument.
     *
     * @param int $equipmentId
     * @param UploadedFile $file
     * @param string $type The document type (manual, receipt, etc.)
     * @param string|null $title
     * @param string|null $version
     * @param string|null $notes
     * @param string $disk
     * @return EquipmentDocument
     */
    public function createEquipmentDocument(
        int $equipmentId,
        UploadedFile $file,
        string $type,
        ?string $title = null,
        ?string $version = null,
        ?string $notes = null,
        string $disk = 'public'
    ): EquipmentDocument {
        $path = $this->storeFile($file, 'equipment-documents', $disk);

        return EquipmentDocument::create([
            'equipment_id' => $equipmentId,
            'uploaded_by_user_id' => auth()->id(),
            'type' => $type,
            'title' => $title ?? $file->getClientOriginalName(),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'version' => $version,
            'notes' => $notes,
        ]);
    }

    /**
     * Store a file with a hashed name to prevent collisions.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $disk
     * @return string
     */
    protected function storeFile(UploadedFile $file, string $directory, string $disk): string
    {
        return $file->store($directory, $disk);
    }

    /**
     * Delete an attachment and its file.
     *
     * @param Attachment $attachment
     * @param string $disk
     * @return bool
     */
    public function deleteAttachment(Attachment $attachment, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($attachment->file_path)) {
            Storage::disk($disk)->delete($attachment->file_path);
        }

        return $attachment->delete();
    }

    /**
     * Delete an equipment document and its file.
     *
     * @param EquipmentDocument $document
     * @param string $disk
     * @return bool
     */
    public function deleteEquipmentDocument(EquipmentDocument $document, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($document->file_path)) {
            Storage::disk($disk)->delete($document->file_path);
        }

        return $document->delete();
    }
}
