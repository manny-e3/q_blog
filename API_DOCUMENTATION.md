# Q-Blogs API Documentation

**Base URL:** `/api/v1`  
**Authentication:** HTTP Basic Auth (`basic.auth` middleware) where required.

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Public Articles](#2-public-articles)
3. [CMS Article Management](#3-cms-article-management)
4. [My Articles](#4-my-articles)
5. [Admin Articles](#5-admin-articles)
6. [Approval Workflow](#6-approval-workflow)
7. [Categories](#7-categories)
8. [Tags](#8-tags)
9. [Media](#9-media)
10. [Notifications](#10-notifications)
11. [Analytics](#11-analytics)
12. [Newsletter](#12-newsletter)
13. [System Utilities](#13-system-utilities)

---

## 1. Authentication

### POST `/auth/login`
Authenticate a user and receive a token.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "secret"
}
```

**Response `200`:**
```json
{
  "token": "...",
  "user": { }
}
```

---

### POST `/auth/refresh`
Refresh an existing authentication token.

**Response `200`:**
```json
{
  "token": "..."
}
```

---

### POST `/auth/logout`
Invalidate the current token.

**Response `200`:**
```json
{
  "message": "Logged out successfully."
}
```

---

### GET `/auth/me` 🔒
Return the currently authenticated user's profile.

**Response `200`:**
```json
{
  "id": 1,
  "email": "user@example.com",
  "role": "INPUTTER"
}
```

---

## 2. Public Articles

### GET `/articles/featured`
Retrieve the featured article plus the 5 latest non-featured articles.

**Response `200`:**
```json
{
  "featured": { },
  "latest": [ ]
}
```

---

### GET `/articles`
List published articles with optional filters and pagination.

**Query Parameters:**

| Parameter  | Type   | Description                                      |
|------------|--------|--------------------------------------------------|
| `category` | string | Filter by category slug or ID                    |
| `author`   | string | Filter by inputter (author) ID                   |
| `tag`      | string | Filter by tag slug or ID                         |
| `sort`     | string | `latest` (default), `oldest`, `views`, `shares`  |
| `limit`    | int    | Items per page (default: 12)                     |

**Response `200`:** Paginated list of articles.

---

### GET `/articles/search`
Full-text search across published articles (title, content, author name, tags).

**Query Parameters:**

| Parameter | Type   | Description    |
|-----------|--------|----------------|
| `q`       | string | Search keyword |

**Response `200`:** Array of matching articles.

---

### GET `/articles/{slug}`
Retrieve a single published article by its slug.

**Response `200`:** Article object.
**Response `404`:** `{ "message": "Article not found." }`

---

### GET `/articles/{id}/related`
Retrieve related articles based on the same category or shared tags.

**Response `200`:** Array of up to 4 related articles.

---

### GET `/articles/{id}/pdf`
Download a generated PDF of the article.

**Response `200`:** Binary PDF (`application/pdf`).

---

### POST `/articles/{id}/view`
Track a page view for an article.

**Response `200`:** Updated article.

---

### POST `/articles/{id}/share`
Track a share event for an article.

**Response `200`:** Updated article.

---

## 3. CMS Article Management

All endpoints in this section require **authentication** 🔒.

---

### POST `/cms/articles` 🔒
Create a new article.

> **Notification Behaviour:** If `authoriser_id` is provided in the payload, only that specific authoriser will receive an email notification when the article is in `pending` status. If `authoriser_id` is omitted, no email notification is sent — notifications are no longer broadcast to all authorisers.

**Request Body:**

| Field           | Type    | Required | Description                                                         |
|-----------------|---------|----------|---------------------------------------------------------------------|
| `title`         | string  | Yes      | Article title (max 255 chars)                                       |
| `content`       | string  | Yes      | Article body content                                                |
| `summary`       | string  | No       | Short article summary                                               |
| `category_id`   | integer | Yes      | Must exist in `categories` table                                    |
| `tags`          | array   | No       | Array of tag IDs (each must exist in `tags` table)                  |
| `is_featured`   | boolean | No       | Whether to feature the article (default: `false`)                   |
| `inputter_id`   | integer | No       | ID of the article author; falls back to `user_id` or authenticated user |
| `authoriser_id` | integer | No       | ID of the **specific authoriser** to be notified for approval       |
| `status`        | string  | No       | `draft`, `pending`, or `published` (default: `pending`)             |

**Business Rules:**
- If the inputting user has an `Authoriser` role, the article defaults to `published` status.
- Otherwise, the article defaults to `pending` status.
- An explicit `status` in the payload overrides the default.
- If `authoriser_id` is provided and the article is `pending`, only the specified authoriser receives an approval email.
- If `authoriser_id` is omitted, the article is saved but no email notification is dispatched.

**Response `201`:** The created article object.
**Response `400`:**
```json
{ "message": "Inputter ID is required." }
```
or
```json
{ "message": "Invalid Authoriser ID." }
```

---

### GET `/cms/articles/{id}` 🔒
Retrieve details of a single article by ID for editing. Only the article's inputter or an AUTHORISER may retrieve.

**Response `200`:** Article object.
**Response `403`:** `{ "message": "Forbidden." }`
**Response `404`:** `{ "message": "Article not found." }`

---

### PATCH `/cms/articles/{id}` 🔒
Update an existing article. Only the article's inputter or an AUTHORISER may update.

**Request Body (all fields optional):**

| Field         | Type    | Description                              |
|---------------|---------|------------------------------------------|
| `title`       | string  | Article title (max 255 chars)            |
| `content`     | string  | Article body content                     |
| `summary`     | string  | Short article summary                    |
| `category_id` | integer | Must exist in `categories` table         |
| `tags`        | array   | Array of tag IDs                         |
| `is_featured` | boolean | Featured flag                            |
| `status`      | string  | `draft`, `pending`, or `published`       |

**Response `200`:** Updated article object.
**Response `403`:** `{ "message": "Forbidden." }`
**Response `404`:** `{ "message": "Article not found." }`

---

### DELETE `/cms/articles/{id}` 🔒
Delete an article. Only the article's inputter or an AUTHORISER may delete.

**Response `200`:** `{ "message": "Article deleted successfully." }`
**Response `403`:** `{ "message": "Forbidden." }`
**Response `404`:** `{ "message": "Article not found." }`

---

### POST `/cms/articles/{id}/save-draft` 🔒
Set an article's status back to `draft`.

**Response `200`:**
```json
{
  "message": "Article status updated to draft.",
  "article": { }
}
```

---

### POST `/cms/articles/preview` 🔒
Render Markdown content to HTML for preview purposes.

**Request Body:**

| Field     | Type   | Required | Description            |
|-----------|--------|----------|------------------------|
| `content` | string | Yes      | Markdown content string |

**Response `200`:**
```json
{
  "html": "<p>Rendered HTML...</p>"
}
```

---

### POST `/cms/articles/{id}/publish` 🔒
Publish or submit an article for approval.

- **AUTHORISER** role: article is published immediately.
- **INPUTTER** role: article status is set to `pending`; if an `authoriser_id` is already assigned to the article, only that authoriser receives an email notification.

**Response `200`:**
```json
{
  "message": "Article published directly.",
  "article": { }
}
```
**Response `403`:** `{ "message": "Forbidden." }`

---

### POST `/cms/articles/{id}/unpublish` 🔒
Unpublish an article, resetting its status to `draft`.

**Response `200`:**
```json
{
  "message": "Article unpublished and status reset to draft.",
  "article": { }
}
```

---

## 4. My Articles

All endpoints require **authentication** 🔒. Returns articles belonging to the authenticated user.

| Method | Endpoint                         | Description                                   |
|--------|----------------------------------|-----------------------------------------------|
| GET    | `/cms/my-articles/published`     | Authenticated user's published articles       |
| GET    | `/cms/my-articles/drafts`        | Authenticated user's draft articles           |
| GET    | `/cms/my-articles/pending`       | Authenticated user's pending articles         |
| GET    | `/cms/my-articles/rejected`      | Authenticated user's rejected articles        |

**Response `200`:** Array of article objects.

---

## 5. Admin Articles

All endpoints require **authentication** 🔒. Returns articles across all authors.

| Method | Endpoint                              | Description                      |
|--------|---------------------------------------|----------------------------------|
| GET    | `/cms/admin/articles/published`       | All published articles           |
| GET    | `/cms/admin/articles/unpublished`     | All unpublished (draft) articles |
| GET    | `/cms/admin/articles/pending`         | All pending articles             |
| GET    | `/cms/admin/articles/rejected`        | All rejected articles            |

**Response `200`:** Array of article objects.

---

## 6. Approval Workflow

All endpoints require **authentication** 🔒.

---

### POST `/approvals/{articleId}/approve` 🔒
Approve a pending article and publish it.

**Request Body:**

| Field    | Type   | Required | Description                    |
|----------|--------|----------|--------------------------------|
| `reason` | string | No       | Optional reason for approval   |

**Response `200`:**
```json
{
  "message": "Article approved and published.",
  "article": { }
}
```

---

### POST `/approvals/{articleId}/reject` 🔒
Reject a pending article.

**Request Body:**

| Field    | Type   | Required | Description          |
|----------|--------|----------|----------------------|
| `reason` | string | No       | Reason for rejection |

**Response `200`:**
```json
{
  "message": "Article rejected.",
  "article": { }
}
```

---

### GET `/approvals/{articleId}/history` 🔒
Retrieve the approval/rejection history for an article.

**Response `200`:** Array of approval history records.

---

### GET `/approvals/pending/count` 🔒
Return the count of articles currently awaiting approval.

**Response `200`:**
```json
{
  "count": 5
}
```

---

## 7. Categories

### GET `/categories`
List all active categories. (Public)

**Response `200`:** Array of category objects.

---

### GET `/categories/{id}`
Get a single category by ID. (Public)

**Response `200`:** Category object.
**Response `404`:** `{ "message": "Category not found." }`

---

### POST `/categories` 🔒
Create a new category.

**Request Body:**

| Field         | Type   | Required | Description          |
|---------------|--------|----------|----------------------|
| `name`        | string | Yes      | Category name        |
| `description` | string | No       | Category description |

**Response `201`:** Created category object.

---

### PATCH `/categories/{id}` 🔒
Update an existing category.

**Response `200`:** Updated category object.

---

### PATCH `/categories/{id}/deactivate` 🔒
Deactivate a category.

**Response `200`:** `{ "message": "Category deactivated." }`

---

### DELETE `/categories/{id}` 🔒
Delete an existing category.

**Response `200`:** `{ "message": "Category deleted successfully." }`
**Response `404`:** `{ "message": "Category not found." }`

---

## 8. Tags

### GET `/tags`
List all tags. (Public)

**Response `200`:** Array of tag objects.

---

### POST `/tags` 🔒
Create a new tag.

**Request Body:**

| Field  | Type   | Required | Description |
|--------|--------|----------|-------------|
| `name` | string | Yes      | Tag name    |

**Response `201`:** Created tag object.

---

### PATCH `/tags/{id}` 🔒
Update a tag.

**Response `200`:** Updated tag object.

---

### DELETE `/tags/{id}` 🔒
Delete a tag.

**Response `200`:** `{ "message": "Tag deleted successfully." }`

---

## 9. Media

All endpoints require **authentication** 🔒.

---

### POST `/media/upload` 🔒
Upload a media file.

**Request:** `multipart/form-data`

| Field  | Type | Required | Description        |
|--------|------|----------|--------------------|
| `file` | file | Yes      | The file to upload |

**Response `201`:** Media object with `url`, `type`, `size`, etc.

---

### GET `/media` 🔒
List all uploaded media files.

**Response `200`:** Array of media objects.

---

### DELETE `/media/{id}` 🔒
Delete a media file.

**Response `200`:** `{ "message": "Media deleted successfully." }`

---

### PATCH `/media/{id}/alt-text` 🔒
Update the alt text for a media file.

**Request Body:**

| Field      | Type   | Required | Description        |
|------------|--------|----------|--------------------|
| `alt_text` | string | Yes      | New alt text value |

**Response `200`:** Updated media object.

---

## 10. Notifications

All endpoints require **authentication** 🔒.

---

### GET `/notifications` 🔒
Retrieve all notifications for the authenticated user.

**Response `200`:** Array of notification objects.

---

### PATCH `/notifications/{id}/read` 🔒
Mark a single notification as read.

**Response `200`:** Updated notification object.

---

### PATCH `/notifications/read-all` 🔒
Mark all notifications for the authenticated user as read.

**Response `200`:** `{ "message": "All notifications marked as read." }`

---

## 11. Analytics

All endpoints require **authentication** 🔒.

| Method | Endpoint                         | Description                                  |
|--------|----------------------------------|----------------------------------------------|
| GET    | `/analytics/dashboard`           | Overview analytics dashboard                 |
| GET    | `/analytics/articles`            | Article-level analytics                      |
| GET    | `/analytics/top-articles`        | Top articles by views/shares                 |
| GET    | `/analytics/top-categories`      | Top categories by article count              |
| GET    | `/analytics/top-authors`         | Top authors by article count or engagement   |
| GET    | `/analytics/traffic-sources`     | Traffic source breakdown                     |
| GET    | `/analytics/reading-time`        | Average reading time stats                   |
| GET    | `/analytics/shares`              | Share analytics                              |
| GET    | `/analytics/export/csv`          | Export analytics as CSV                      |
| GET    | `/analytics/export/pdf`          | Export analytics as PDF                      |

---

## 12. Newsletter

### POST `/newsletter/subscribe`
Subscribe an email address to the newsletter.

**Request Body:**

| Field   | Type   | Required | Description          |
|---------|--------|----------|----------------------|
| `email` | string | Yes      | Email to subscribe   |

**Response `200`:** `{ "message": "Subscribed successfully." }`

---

### POST `/newsletter/verify-captcha`
Verify a CAPTCHA token before newsletter subscription.

**Request Body:**

| Field   | Type   | Required | Description      |
|---------|--------|----------|------------------|
| `token` | string | Yes      | CAPTCHA token    |

**Response `200`:** `{ "success": true }`

---

### GET `/newsletter/check`
Check whether an email is already subscribed.

**Query Parameters:**

| Parameter | Type   | Description     |
|-----------|--------|-----------------|
| `email`   | string | Email to check  |

**Response `200`:** `{ "subscribed": true }`

---

### POST `/newsletter/sync` 🔒
Sync newsletter subscriptions with an external mailing service.

**Response `200`:** `{ "message": "Sync completed." }`

---

### GET `/cms/subscribers` 🔒
Retrieve a paginated list of newsletter subscribers.

**Response `200`:** Paginated subscribers structure.

---

## 13. System Utilities

### POST `/system/generate-slug`
Generate a URL-friendly slug from a given title.

**Request Body:**

| Field   | Type   | Required | Description      |
|---------|--------|----------|------------------|
| `title` | string | Yes      | Title to slugify |

**Response `200`:**
```json
{
  "slug": "my-article-title"
}
```

---

### POST `/system/reading-time`
Calculate the estimated reading time for content.

**Request Body:**

| Field     | Type   | Required | Description          |
|-----------|--------|----------|----------------------|
| `content` | string | Yes      | Content to measure   |

**Response `200`:**
```json
{
  "reading_time_minutes": 4
}
```

---

### GET `/health`
Health check endpoint.

**Response `200`:**
```json
{
  "status": "ok",
  "timestamp": "2026-07-05T14:00:00Z"
}
```

---

## Error Responses

| Status | Description                                 |
|--------|---------------------------------------------|
| `400`  | Bad request / validation error              |
| `401`  | Unauthenticated — missing or invalid token  |
| `403`  | Forbidden — insufficient permissions        |
| `404`  | Resource not found                          |
| `422`  | Unprocessable entity — failed validation    |
| `500`  | Internal server error                       |

---

## Changelog

### 2026-07-05
- **`POST /cms/articles`** — Updated notification behaviour: `authoriser_id` must be explicitly passed in the request payload to target a specific authoriser for email notification. If omitted, no notification email is sent. The previous fallback of broadcasting to **all** authorisers has been removed.
- **`POST /cms/articles/{id}/publish`** — Same notification change applied: only the article's assigned `authoriser_id` is notified when an INPUTTER submits for approval.
