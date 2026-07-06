<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Str;

class TagService
{
    /**
     * Get all tags.
     */
    public function getAllTags()
    {
        return Tag::all();
    }

    /**
     * Find tag by ID.
     */
    public function findTag(int $id): ?Tag
    {
        return Tag::find($id);
    }

    /**
     * Create tag.
     */
    public function createTag(array $data): Tag
    {
        return Tag::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);
    }

    /**
     * Update tag.
     */
    public function updateTag(Tag $tag, array $data): Tag
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $tag->update($data);

        return $tag;
    }

    /**
     * Delete tag.
     */
    public function deleteTag(Tag $tag): bool
    {
        return $tag->delete();
    }
}
