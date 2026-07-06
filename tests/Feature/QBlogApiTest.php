<?php

namespace Tests\Feature;

use Illuminate\Auth\GenericUser;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QBlogApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $author;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Mail::fake();

        // Instantiate mock users in-memory
        $this->admin = new GenericUser([
            'id' => 1,
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'role' => 'AUTHORISER',
            'status' => 'active'
        ]);

        $this->author = new GenericUser([
            'id' => 2,
            'name' => 'Test Author',
            'email' => 'author@test.com',
            'role' => 'INPUTTER',
            'status' => 'active'
        ]);

        // Mock ExternalUserService
        $mockUserService = $this->createMock(\App\Services\ExternalUserService::class);
        
        $usersCollection = collect([
            1 => [
                'id' => 1,
                'firstname' => 'Test',
                'lastname' => 'Admin',
                'email' => 'admin@test.com',
                'role' => 'AUTHORISER',
                'status' => 'active'
            ],
            2 => [
                'id' => 2,
                'firstname' => 'Test',
                'lastname' => 'Author',
                'email' => 'author@test.com',
                'role' => 'INPUTTER',
                'status' => 'active'
            ]
        ]);

        $mockUserService->method('getAllUsers')->willReturn($usersCollection);
        
        $mockUserService->method('getUserById')->willReturnCallback(function ($id) use ($usersCollection) {
            return $usersCollection->get($id);
        });

        $this->app->instance(\App\Services\ExternalUserService::class, $mockUserService);

        $this->category = Category::create([
            'name' => 'Market Review',
            'slug' => 'market-review',
            'status' => 'active'
        ]);
    }

    /**
     * Test health check.
     */
    public function test_health_check_endpoint()
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'database',
                'timestamp'
            ]);
    }

    /**
     * Test Basic Auth protection.
     */
    public function test_basic_auth_fails_with_invalid_credentials()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('invalid@test.com:wrongpassword')
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid email or password.'
            ]);
    }

    public function test_basic_auth_succeeds_with_valid_credentials()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'email' => 'author@test.com',
                'role' => 'INPUTTER'
            ]);
    }

    /**
     * Test Article submission and approval workflow.
     */
    public function test_article_lifecycle_workflow()
    {
        // 1. Inputter creates draft article
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->postJson('/api/v1/cms/articles', [
            'title' => 'New Yield Curve Report',
            'content' => 'Sample markdown Curve Content.',
            'category_id' => $this->category->id,
            'status' => 'draft',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'New Yield Curve Report',
                'status' => 'draft'
            ]);

        $articleId = $response->json('id');

        // 2. Submit for approval
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->postJson("/api/v1/cms/articles/{$articleId}/publish");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'pending'
            ]);

        // 3. Admin rejects article
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->postJson("/api/v1/approvals/{$articleId}/reject", [
            'reason' => 'Formatting curve issue.'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'rejected',
                'reject_reason' => 'Formatting curve issue.'
            ]);

        // 4. Submit for approval again
        $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->postJson("/api/v1/cms/articles/{$articleId}/publish");

        // 5. Admin approves and publishes article
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->postJson("/api/v1/approvals/{$articleId}/approve");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'published'
            ]);

        $slug = $response->json('article.slug') ?? $response->json('slug');

        // 6. Public GET single-article by slug
        $response = $this->getJson("/api/v1/articles/{$slug}");
        $response->assertStatus(200)
            ->assertJsonFragment([
                'slug' => $slug,
                'status' => 'published'
            ]);
    }

    public function test_create_article_with_valid_authoriser()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->postJson('/api/v1/cms/articles', [
            'title' => 'Article with Authoriser',
            'content' => 'Sample content.',
            'category_id' => $this->category->id,
            'authoriser_id' => 1, // ID of Admin (AUTHORISER)
            'status' => 'pending',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'Article with Authoriser',
                'authoriser_id' => 1,
                'status' => 'pending'
            ]);

        // Assert that a notification database record was created for the authoriser (user_id = 1)
        $this->assertDatabaseHas('notifications', [
            'user_id' => 1,
            'title' => 'New Article Awaiting Approval',
        ]);
    }

    public function test_create_article_with_invalid_authoriser()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->postJson('/api/v1/cms/articles', [
            'title' => 'Article with Invalid Authoriser',
            'content' => 'Sample content.',
            'category_id' => $this->category->id,
            'authoriser_id' => 999, // Non-existent user
            'status' => 'pending',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid Authoriser ID.'
            ]);
    }

    public function test_create_article_with_any_valid_user_as_authoriser()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->postJson('/api/v1/cms/articles', [
            'title' => 'Article with Any Valid User as Authoriser',
            'content' => 'Sample content.',
            'category_id' => $this->category->id,
            'authoriser_id' => 2, // ID of Test Author
            'status' => 'pending',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'Article with Any Valid User as Authoriser',
                'authoriser_id' => 2,
                'status' => 'pending'
            ]);
    }

    public function test_cms_get_single_article_by_id()
    {
        $article = Article::create([
            'title' => 'CMS Edit Test',
            'slug' => 'cms-edit-test',
            'content' => 'Content here.',
            'category_id' => $this->category->id,
            'inputter_id' => 2, // Test Author
            'status' => 'draft'
        ]);

        // 1. Unauthenticated request
        $response = $this->getJson("/api/v1/cms/articles/{$article->id}");
        $response->assertStatus(401);

        // 2. Authenticated request (author of the article)
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->getJson("/api/v1/cms/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'CMS Edit Test'
            ]);
    }

    public function test_category_delete()
    {
        $newCategory = Category::create([
            'name' => 'Tech News',
            'slug' => 'tech-news',
            'status' => 'active'
        ]);

        // 1. Unauthenticated request
        $response = $this->deleteJson("/api/v1/categories/{$newCategory->id}");
        $response->assertStatus(401);

        // 2. Authenticated request
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->deleteJson("/api/v1/categories/{$newCategory->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Category deleted successfully.'
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $newCategory->id
        ]);
    }

    public function test_cms_get_subscribers()
    {
        \App\Models\NewsletterSubscription::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'consent_given' => true
        ]);

        // 1. Unauthenticated request
        $response = $this->getJson('/api/v1/cms/subscribers');
        $response->assertStatus(401);

        // 2. Authenticated request
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->getJson('/api/v1/cms/subscribers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'consent_given',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'current_page',
                'total'
            ]);
    }

    public function test_cms_unpublish_authorization()
    {
        // 1. Create article owned by user 3 (another user)
        $article = Article::create([
            'title' => 'Other User Article',
            'slug' => 'other-user-article',
            'content' => 'Content here.',
            'category_id' => $this->category->id,
            'inputter_id' => 3,
            'status' => 'published'
        ]);

        // 2. Author (inputter, id=2) attempts to unpublish article owned by inputter_id=3 -> Forbidden (403)
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->postJson("/api/v1/cms/articles/{$article->id}/unpublish");

        $response->assertStatus(403);

        // 3. Admin (authoriser, id=1) attempts to unpublish article -> Succeeds (200)
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->postJson("/api/v1/cms/articles/{$article->id}/unpublish");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'draft'
            ]);
    }
}

