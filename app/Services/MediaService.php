<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    /**
     * Get all media library items.
     */
    public function getLibrary()
    {
        return Media::latest()->get();
    }

    /**
     * Find media by ID.
     */
    public function findMedia(int $id): ?Media
    {
        return Media::find($id);
    }

    /**
     * Upload and create media.
     */
    public function uploadMedia(UploadedFile $file, ?string $altText = null): Media
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('media', $filename, 'public');

        return Media::create([
            'filename' => $file->getClientOriginalName(),
            'path' => Storage::url($path),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'alt_text' => $altText,
        ]);
    }

    /**
     * Delete media record and associated physical file.
     */
    public function deleteMedia(Media $media): bool
    {
        $relativePath = str_replace('/storage/', '', $media->path);
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }

        return $media->delete();
    }

    /**
     * Update alt text of media.
     */
    public function updateAltText(Media $media, string $altText): Media
    {
        $media->update([
            'alt_text' => $altText
        ]);

        return $media;
    }
}
