<?php

namespace Tests\Feature;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating an author without authentication.
     */
    public function test_create_author_unauthenticated()
    {
        $response = $this->postJson('/api/v1/authors', [
            'name' => 'Faith Admin',
            'email' => 'faith.idebi@fmdqgroup.com'
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test creating an author with authentication and all fields.
     */
    public function test_create_author_authenticated_success()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->postJson('/api/v1/authors', [
            'id' => 534,
            'name' => 'Faith Admin',
            'email' => 'faith.idebi@fmdqgroup.com',
            'title' => 'Head of Market Infrastructure',
            'bio' => 'Mr. Afolabi is the Head, Market Architecture Division...',
            'expertise' => ['Fixed Income Markets', 'Yield Analysis', 'Monetary Policy'],
            'linkedin_url' => 'https://www.linkedin.com/in/faith-admin',
            'twitter_url' => 'https://twitter.com/faith_admin',
            'facebook_url' => 'https://facebook.com/faith_admin',
            'instagram_url' => 'https://instagram.com/faith_admin',
            'website_url' => 'https://faithadmin.com'
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'id' => 534,
                'name' => 'Faith Admin',
                'email' => 'faith.idebi@fmdqgroup.com',
                'title' => 'Head of Market Infrastructure',
                'bio' => 'Mr. Afolabi is the Head, Market Architecture Division...',
                'expertise' => ['Fixed Income Markets', 'Yield Analysis', 'Monetary Policy'],
                'linkedin_url' => 'https://www.linkedin.com/in/faith-admin',
                'twitter_url' => 'https://twitter.com/faith_admin',
                'facebook_url' => 'https://facebook.com/faith_admin',
                'instagram_url' => 'https://instagram.com/faith_admin',
                'website_url' => 'https://faithadmin.com'
            ]);

        $this->assertDatabaseHas('authors', [
            'id' => 534,
            'email' => 'faith.idebi@fmdqgroup.com',
            'twitter_url' => 'https://twitter.com/faith_admin'
        ]);
    }

    /**
     * Test creating an author with duplicate email validation error.
     */
    public function test_create_author_duplicate_email()
    {
        Author::create([
            'name' => 'Existing Author',
            'email' => 'faith.idebi@fmdqgroup.com'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->postJson('/api/v1/authors', [
            'name' => 'Faith Admin',
            'email' => 'faith.idebi@fmdqgroup.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test retrieving all authors.
     */
    public function test_get_all_authors()
    {
        Author::create([
            'name' => 'Author One',
            'email' => 'author1@example.com'
        ]);
        Author::create([
            'name' => 'Author Two',
            'email' => 'author2@example.com'
        ]);

        $response = $this->getJson('/api/v1/authors');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Author One'])
            ->assertJsonFragment(['name' => 'Author Two']);
    }

    /**
     * Test retrieving an author by email.
     */
    public function test_get_author_by_email_success()
    {
        Author::create([
            'name' => 'Faith Admin',
            'email' => 'faith.idebi@fmdqgroup.com'
        ]);

        $response = $this->getJson('/api/v1/authors/email?email=faith.idebi@fmdqgroup.com');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Faith Admin',
                'email' => 'faith.idebi@fmdqgroup.com'
            ]);
    }

    /**
     * Test retrieving non-existent author by email returns 404.
     */
    public function test_get_author_by_email_not_found()
    {
        $response = $this->getJson('/api/v1/authors/email?email=missing@example.com');
        $response->assertStatus(404);
    }

    /**
     * Test editing an author without authentication.
     */
    public function test_edit_author_unauthenticated()
    {
        $author = Author::create([
            'name' => 'Faith Admin',
            'email' => 'faith.idebi@fmdqgroup.com'
        ]);

        $response = $this->patchJson("/api/v1/authors/{$author->id}", [
            'name' => 'Faith Updated'
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test editing an author with authentication succeeds.
     */
    public function test_edit_author_authenticated_success()
    {
        $author = Author::create([
            'name' => 'Faith Admin',
            'email' => 'faith.idebi@fmdqgroup.com',
            'title' => 'Old Title'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->patchJson("/api/v1/authors/{$author->id}", [
            'name' => 'Faith Updated',
            'title' => 'New Title',
            'twitter_url' => 'https://twitter.com/faith_new'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $author->id,
                'name' => 'Faith Updated',
                'title' => 'New Title',
                'twitter_url' => 'https://twitter.com/faith_new'
            ]);

        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => 'Faith Updated',
            'title' => 'New Title'
        ]);
    }

    /**
     * Test editing an author returns 404 if author does not exist.
     */
    public function test_edit_author_not_found()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->patchJson('/api/v1/authors/9999', [
            'name' => 'Faith Updated'
        ]);

        $response->assertStatus(404);
    }
}
