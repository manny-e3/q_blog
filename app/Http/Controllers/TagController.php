<?php

namespace App\Http\Controllers;

use App\Services\TagService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    protected $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * Get all tags.
     */
    public function index()
    {
        $tags = $this->tagService->getAllTags();
        return response()->json($tags);
    }

    /**
     * Create tag.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        $tag = $this->tagService->createTag($validated);

        return response()->json($tag, 201);
    }

    /**
     * Update tag.
     */
    public function update(Request $request, $id)
    {
        $tag = $this->tagService->findTag($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found.'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('tags', 'name')->ignore($tag->id)],
        ]);

        $updatedTag = $this->tagService->updateTag($tag, $validated);

        return response()->json($updatedTag);
    }

    /**
     * Delete tag.
     */
    public function destroy($id)
    {
        $tag = $this->tagService->findTag($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found.'], 404);
        }

        $this->tagService->deleteTag($tag);

        return response()->json([
            'message' => 'Tag deleted successfully.'
        ]);
    }
}
