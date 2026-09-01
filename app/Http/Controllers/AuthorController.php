<?php

namespace App\Http\Controllers;

use App\Services\AuthorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthorController extends Controller
{
    protected $authorService;

    public function __construct(AuthorService $authorService)
    {
        $this->authorService = $authorService;
    }

    /**
     * Get all authors.
     */
    public function index()
    {
        return response()->json($this->authorService->getAllAuthors());
    }

    /**
     * Get author by email.
     */
    public function showByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $author = $this->authorService->getAuthorByEmail($request->email);

        if (!$author) {
            return response()->json(['message' => 'Author not found.'], 404);
        }

        return response()->json($author);
    }

    /**
     * Create a new author.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'sometimes|integer|unique:authors,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:authors,email',
            'title' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'expertise' => 'nullable|array',
            'expertise.*' => 'string',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
        ]);

        $author = $this->authorService->createAuthor($validated);

        return response()->json($author, 201);
    }

    /**
     * Update an existing author.
     */
    public function update(Request $request, $id)
    {
        $author = $this->authorService->getAuthorById((int)$id);

        if (!$author) {
            return response()->json(['message' => 'Author not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('authors', 'email')->ignore($author->id),
            ],
            'title' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'expertise' => 'nullable|array',
            'expertise.*' => 'string',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
        ]);

        $updatedAuthor = $this->authorService->updateAuthor($author, $validated);

        return response()->json($updatedAuthor);
    }
}
