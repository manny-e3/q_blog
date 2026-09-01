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

        // Assert submission confirmation notification was sent to inputter
        $this->assertDatabaseHas('notifications', [
            'user_id' => 2,
            'title' => 'Article Submitted Awaiting Approval',
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

        // Assert rejection email was sent
        \Illuminate\Support\Facades\Mail::assertSent(function (\App\Mail\ArticleResolutionMail $mail) {
            return $mail->hasTo('author@test.com') && 
                   $mail->status === 'rejected';
        });

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

        // Assert approval email was sent
        \Illuminate\Support\Facades\Mail::assertSent(function (\App\Mail\ArticleResolutionMail $mail) {
            return $mail->hasTo('author@test.com') && 
                   $mail->status === 'published';
        });

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

    public function test_create_and_update_article_with_featured_image()
    {
        $file = \Illuminate\Http\UploadedFile::fake()->image('featured.jpg');
        $createdFiles = [];

        // 1. Create Article with uploaded featured image
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->postJson('/api/v1/cms/articles', [
            'title' => 'Article with Featured Image',
            'content' => 'Sample content with image.',
            'category_id' => $this->category->id,
            'status' => 'draft',
            'featured_image' => $file
        ]);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('featured_image'));
        
        $featuredImagePath = $response->json('featured_image');
        $filename = basename(parse_url($featuredImagePath, PHP_URL_PATH));
        $this->assertFileExists(public_path('featured_image/' . $filename));
        $createdFiles[] = public_path('featured_image/' . $filename);

        $articleId = $response->json('id');

        // 2. Update Article with a string URL
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->patchJson("/api/v1/cms/articles/{$articleId}", [
            'featured_image' => 'https://example.com/other-image.jpg'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'featured_image' => 'https://example.com/other-image.jpg'
            ]);

        // 3. Update Article with a new uploaded file
        $newFile = \Illuminate\Http\UploadedFile::fake()->image('new-featured.jpg');
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->patchJson("/api/v1/cms/articles/{$articleId}", [
            'featured_image' => $newFile
        ]);

        $response->assertStatus(200);
        $newFeaturedImagePath = $response->json('featured_image');
        $this->assertNotEquals($featuredImagePath, $newFeaturedImagePath);
        
        $newFilename = basename(parse_url($newFeaturedImagePath, PHP_URL_PATH));
        $this->assertFileExists(public_path('featured_image/' . $newFilename));
        $createdFiles[] = public_path('featured_image/' . $newFilename);

        // Cleanup
        foreach ($createdFiles as $filePath) {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    public function test_create_and_update_article_with_base64_featured_image()
    {
        $base64Image = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
        $createdFiles = [];

        // 1. Create Article with base64 image
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->postJson('/api/v1/cms/articles', [
            'title' => 'Article with Base64 Image',
            'content' => 'Sample content.',
            'category_id' => $this->category->id,
            'status' => 'draft',
            'featured_image' => $base64Image
        ]);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('featured_image'));
        
        $featuredImagePath = $response->json('featured_image');
        $this->assertStringContainsString('/featured_image/', $featuredImagePath);
        
        $filename = basename(parse_url($featuredImagePath, PHP_URL_PATH));
        $this->assertFileExists(public_path('featured_image/' . $filename));
        $createdFiles[] = public_path('featured_image/' . $filename);

        $articleId = $response->json('id');

        // 2. Update Article with a new base64 image
        $newBase64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->patchJson("/api/v1/cms/articles/{$articleId}", [
            'featured_image' => $newBase64Image
        ]);

        $response->assertStatus(200);
        $newFeaturedImagePath = $response->json('featured_image');
        $this->assertNotEquals($featuredImagePath, $newFeaturedImagePath);
        
        $newFilename = basename(parse_url($newFeaturedImagePath, PHP_URL_PATH));
        $this->assertFileExists(public_path('featured_image/' . $newFilename));
        $createdFiles[] = public_path('featured_image/' . $newFilename);

        // Cleanup
        foreach ($createdFiles as $filePath) {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    public function test_create_article_with_form_data_stringified_inputs()
    {
        $tag = Tag::create(['name' => 'News', 'slug' => 'news']);

        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->post('/api/v1/cms/articles', [
            'title' => 'Article with Stringified Form Data',
            'content' => 'Sample content.',
            'category_id' => $this->category->id,
            'status' => 'draft',
            'tags' => '[' . $tag->id . ']',
            'is_featured' => 'true'
        ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('is_featured'));
    }

    public function test_cms_get_single_article_by_id()
    {
        $article = Article::create([
            'title' => 'CMS Edit Test',
            'slug' => 'cms-edit-test',
            'content' => 'Content here.',
            'inputter_id' => 2, // Test Author
            'status' => 'draft',
            'category_ids' => [$this->category->id],
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

    public function test_newsletter_subscribe_with_extra_fields()
    {
        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'first_name' => 'Ada',
            'last_name' => 'Obi',
            'email' => 'ada.obi@example.com',
            'consent' => true,
            'organisation' => 'FMDQ Group',
            'role' => 'Analyst',
            'topics' => ['Sustainability', 'Market & Economy'],
            'frequency' => 'Weekly Digest'
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'first_name' => 'Ada',
                'last_name' => 'Obi',
                'email' => 'ada.obi@example.com',
                'organisation' => 'FMDQ Group',
                'role' => 'Analyst',
                'topics' => ['Sustainability', 'Market & Economy'],
                'frequency' => 'Weekly Digest'
            ]);

        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => 'ada.obi@example.com',
            'organisation' => 'FMDQ Group',
            'role' => 'Analyst',
            'frequency' => 'Weekly Digest'
        ]);
    }

    public function test_newsletter_subscribe_validation_errors()
    {
        // 1. Invalid topics (not in the list)
        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'email' => 'invalid.topics@example.com',
            'consent' => true,
            'topics' => ['Random Topic']
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['topics.0']);

        // 2. Invalid frequency (not in the list)
        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'email' => 'invalid.frequency@example.com',
            'consent' => true,
            'frequency' => 'Hourly'
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['frequency']);
    }

    public function test_cms_unpublish_authorization()
    {
        // 1. Create article owned by user 3 (another user)
        $article = Article::create([
            'title' => 'Other User Article',
            'slug' => 'other-user-article',
            'content' => 'Content here.',
            'inputter_id' => 3,
            'status' => 'published',
            'category_ids' => [$this->category->id],
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

    public function test_notification_endpoints()
    {
        // 1. Store notification successfully
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->postJson('/api/v1/notifications', [
            'user_id' => 2,
            'title' => 'Manual Alert',
            'message' => 'This is a test notification.'
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Notification created successfully.'
            ])
            ->assertJsonStructure([
                'message',
                'notification' => [
                    'id',
                    'user_id',
                    'title',
                    'message',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => 2,
            'title' => 'Manual Alert',
            'message' => 'This is a test notification.'
        ]);

        $notificationId = $response->json('notification.id');

        // 2. Store notification for non-existent user -> 404
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->postJson('/api/v1/notifications', [
            'user_id' => 999,
            'title' => 'Manual Alert',
            'message' => 'This is a test notification.'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'User not found.'
            ]);

        // 3. Validation fails for missing fields -> 422
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->postJson('/api/v1/notifications', [
            'user_id' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'message']);

        // 4. Create another notification for user_id = 1
        $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->postJson('/api/v1/notifications', [
            'user_id' => 1,
            'title' => 'Admin Alert',
            'message' => 'This is for admin.'
        ]);

        // 5. Retrieve notifications globally -> should return both notifications (count = 2)
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(2);

        // 6. Retrieve notifications for user (id = 2) -> should have only 1 notification (user_id = 2)
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->getJson('/api/v1/notifications/user/2');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id' => $notificationId,
                'user_id' => 2,
                'title' => 'Manual Alert',
                'read_at' => null
            ]);

        // 7. Mark single notification as read -> 200
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->patchJson("/api/v1/notifications/{$notificationId}/read");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Notification marked as read.'
            ]);

        $this->assertNotNull($response->json('notification.read_at'));

        // 8. Mark all notifications as read -> 200
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->patchJson('/api/v1/notifications/read-all');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'All notifications marked as read.'
            ]);
    }

    public function test_docs_page_loads()
    {
        $response = $this->get('/docs');
        $response->assertStatus(200);
    }

    public function test_inputter_edit_pending_approval_workflow()
    {
        // 1. Create a published article
        $article = Article::create([
            'title' => 'Original Live Title',
            'slug' => 'original-live-title',
            'content' => 'Original Live Content',
            'summary' => 'Original Live Summary',
            'status' => 'published',
            'inputter_id' => 2, // Test Author
            'authoriser_id' => 1, // Test Admin (AUTHORISER)
            'category_ids' => [$this->category->id],
        ]);

        // 2. Inputter updates article via PATCH
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->patchJson("/api/v1/cms/articles/{$article->id}", [
            'title' => 'Pending Update Title',
            'content' => 'Pending Update Content',
            'summary' => 'Pending Update Summary',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'pending'
            ]);

        // Verify the response contains pending changes
        $this->assertNotNull($response->json('pending_changes'));
        $this->assertEquals('Pending Update Title', $response->json('pending_changes.title'));

        // Verify database live columns are UNCHANGED
        $dbArticle = Article::find($article->id);
        $this->assertEquals('Original Live Title', $dbArticle->title);
        $this->assertEquals('Original Live Content', $dbArticle->content);

        // Verify notification was sent to authoriser
        $this->assertDatabaseHas('notifications', [
            'user_id' => 1,
            'title' => 'Article Update Awaiting Approval',
        ]);

        // 3. Authoriser approves the pending changes
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin@test.com:password')
        ])->postJson("/api/v1/approvals/{$article->id}/approve");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'published'
            ]);

        // Verify database live columns are now updated and pending_changes is cleared
        $approvedArticle = Article::find($article->id);
        $this->assertEquals('Pending Update Title', $approvedArticle->title);
        $this->assertEquals('Pending Update Content', $approvedArticle->content);
        $this->assertNull($approvedArticle->pending_changes);

        // Assert approval resolution email was sent
        \Illuminate\Support\Facades\Mail::assertSent(function (\App\Mail\ArticleResolutionMail $mail) {
            return $mail->hasTo('author@test.com') && 
                   $mail->status === 'published';
        });
    }

    public function test_inputter_updates_assigned_authoriser()
    {
        // Mock ExternalUserService with an additional authoriser (ID = 3)
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
            ],
            3 => [
                'id' => 3,
                'firstname' => 'Second',
                'lastname' => 'Authoriser',
                'email' => 'authoriser3@test.com',
                'role' => 'AUTHORISER',
                'status' => 'active'
            ]
        ]);
        $mockUserService->method('getAllUsers')->willReturn($usersCollection);
        $mockUserService->method('getUserById')->willReturnCallback(function ($id) use ($usersCollection) {
            return $usersCollection->get($id);
        });
        $this->app->instance(\App\Services\ExternalUserService::class, $mockUserService);

        // 1. Create a published article assigned to Authoriser 1
        $article = Article::create([
            'title' => 'Live Title',
            'slug' => 'live-title',
            'content' => 'Live Content',
            'status' => 'published',
            'inputter_id' => 2,
            'authoriser_id' => 1,
            'category_ids' => [$this->category->id],
        ]);

        // 2. Inputter updates article and selects Authoriser 3
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('author@test.com:password')
        ])->patchJson("/api/v1/cms/articles/{$article->id}", [
            'title' => 'New Title',
            'authoriser_id' => 3,
        ]);

        $response->assertStatus(200);

        // Verify live authoriser_id is updated immediately to 3
        $dbArticle = Article::find($article->id);
        $this->assertEquals(3, $dbArticle->authoriser_id);

        // Verify title change is in pending_changes (not live yet)
        $this->assertEquals('Live Title', $dbArticle->title);
        $this->assertEquals('New Title', $dbArticle->pending_changes['title']);

        // Verify notification was sent to the newly selected authoriser (user_id = 3)
        $this->assertDatabaseHas('notifications', [
            'user_id' => 3,
            'title' => 'Article Update Awaiting Approval',
        ]);
    }

    public function test_newsletter_welcome_email_and_unsubscribe()
    {
        \Illuminate\Support\Facades\Mail::fake();

        // 1. Subscribe to the newsletter
        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'consent' => true,
            'topics' => ['Thought Leadership'],
            'frequency' => 'Monthly Highlights'
        ]);

        $response->assertStatus(201);

        // Verify the subscription is in database and status is active
        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => 'jane.doe@example.com',
            'status' => 'active',
        ]);

        // Verify welcome email was sent
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\NewsletterWelcomeMail::class, function ($mail) {
            return $mail->hasTo('jane.doe@example.com') && 
                   $mail->subscription->first_name === 'Jane' && 
                   str_contains($mail->unsubscribeUrl, '/newsletter/unsubscribe');
        });

        // Get the sent mail to retrieve unsubscribe link
        $sentMail = collect(\Illuminate\Support\Facades\Mail::sent(\App\Mail\NewsletterWelcomeMail::class))->first();
        $unsubscribeUrl = $sentMail->unsubscribeUrl;

        // 2. Try to access unsubscribe link with manipulated or invalid signature
        $invalidUrl = $unsubscribeUrl . 'manipulated';
        $response = $this->get($invalidUrl);
        $response->assertStatus(403);

        // Verify subscription is still active
        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => 'jane.doe@example.com',
            'status' => 'active',
        ]);

        // 3. Access unsubscribe link with valid signature
        $response = $this->get($unsubscribeUrl);
        $response->assertStatus(200)
            ->assertViewIs('emails.unsubscribed')
            ->assertSee('Unsubscribed')
            ->assertSee('jane.doe@example.com');

        // Verify subscription status was changed to unsubscribed (not deleted)
        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => 'jane.doe@example.com',
            'status' => 'unsubscribed',
        ]);

        // 4. Try subscribing again with the same email (should reactivate)
        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'first_name' => 'Jane Updated',
            'last_name' => 'Doe Updated',
            'email' => 'jane.doe@example.com',
            'consent' => true,
            'topics' => ['Innovation & Trends'],
            'frequency' => 'Weekly Digest'
        ]);

        $response->assertStatus(201);

        // Verify database is updated and status is active again
        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => 'jane.doe@example.com',
            'first_name' => 'Jane Updated',
            'status' => 'active',
        ]);

        // 5. Try subscribing again while already active (should fail with validation error)
        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'first_name' => 'Jane Duplicate',
            'last_name' => 'Doe Duplicate',
            'email' => 'jane.doe@example.com',
            'consent' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}

