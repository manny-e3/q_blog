<?php

namespace App\Services;

use App\Models\Author;
use Illuminate\Database\Eloquent\Collection;

class AuthorService
{
    /**
     * Get all authors.
     */
    public function getAllAuthors(): Collection
    {
        return Author::all();
    }

    /**
     * Get author by email.
     */
    public function getAuthorByEmail(string $email): ?Author
    {
        return Author::where('email', $email)->first();
    }

    /**
     * Get author by ID.
     */
    public function getAuthorById(int $id): ?Author
    {
        return Author::find($id);
    }

    /**
     * Create a new author.
     */
    public function createAuthor(array $data): Author
    {
        return Author::create($data);
    }

    /**
     * Update an existing author.
     */
    public function updateAuthor(Author $author, array $data): Author
    {
        $author->update($data);
        return $author;
    }
}
