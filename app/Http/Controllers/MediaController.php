<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Upload media file.
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,webp,svg,gif,pdf,mp4|max:20480', // Max 20MB
            'alt_text' => 'nullable|string|max:255'
        ]);

        $media = $this->mediaService->uploadMedia(
            $request->file('file'),
            $validated['alt_text'] ?? null
        );

        return response()->json($media, 201);
    }

    /**
     * Get media library.
     */
    public function index()
    {
        $library = $this->mediaService->getLibrary();
        return response()->json($library);
    }

    /**
     * Delete media file.
     */
    public function destroy($id)
    {
        $media = $this->mediaService->findMedia($id);

        if (!$media) {
            return response()->json(['message' => 'Media file not found.'], 404);
        }

        $this->mediaService->deleteMedia($media);

        return response()->json([
            'message' => 'Media file deleted successfully.'
        ]);
    }

    /**
     * Update media alt text.
     */
    public function updateAltText(Request $request, $id)
    {
        $media = $this->mediaService->findMedia($id);

        if (!$media) {
            return response()->json(['message' => 'Media file not found.'], 404);
        }

        // Support both alt_text and altText key
        $validated = $request->validate([
            'alt_text' => 'sometimes|string|max:255',
            'altText' => 'sometimes|string|max:255',
        ]);

        $altText = $validated['alt_text'] ?? $validated['altText'] ?? '';

        $updatedMedia = $this->mediaService->updateAltText($media, $altText);

        return response()->json([
            'message' => 'Alt text updated successfully.',
            'media' => $updatedMedia
        ]);
    }
}
