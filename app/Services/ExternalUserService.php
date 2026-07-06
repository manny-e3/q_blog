<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class ExternalUserService
{
    protected string $baseUrl;

    public function __construct()
    {
        // Using the URL provided by the user. 
        // In a real scenario, this should be in config/services.php
        $this->baseUrl = 'https://adgdev.fmdqgroup.com/authService/api/apps/22/users/stateless';
    }

    /**
     * Fetch all users from the external service.
     * Caches the result for performance.
     * 
     * @return Collection
     */
    public function getAllUsers(): Collection
    {
        // Return cached users if available
        $cached = Cache::get('external_users');
        if ($cached !== null) {
            return $cached;
        }

        $users = collect();
        $page = 1;
        $lastPage = 1;

        do {
            try {
                $response = Http::timeout(15)
                    ->withBasicAuth('fmdq_admin', 'golddoor2025_secure!')
                    ->get($this->baseUrl, [
                        'page'     => $page,
                        'per_page' => 100,
                    ]);

                if ($response->successful()) {
                    $data = $response->json('data');

                    if (empty($data['data'])) {
                        break;
                    }

                    $users = $users->concat($data['data']);
                    $lastPage = $data['last_page'] ?? 1;
                    $page++;
                } else {
                    \Log::error('External user service returned non-2xx', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    break;
                }
            } catch (\Exception $e) {
                \Log::error('External user service connection error', [
                    'error' => $e->getMessage(),
                ]);
                break;
            }
        } while ($page <= $lastPage);

        \Log::info('External users fetched', [
            'total_users' => $users->count(),
        ]);

        $keyed = $users->keyBy('id');

        // Only cache if we actually got users — don't persist empty results
        if ($keyed->isNotEmpty()) {
            Cache::put('external_users', $keyed, 300);
        }

        return $keyed;
    }

    /**
     * Get a specific user by ID from the cached list.
     * 
     * @param int $id
     * @return array|null
     */
    public function getUserById(int $id): ?array
    {
        $users = $this->getAllUsers();

        // keyBy('id') may store keys as strings when deserialised from the
        // database cache, so try both integer and string variants.
        $user = $users->get($id) ?? $users->get((string) $id);

        if ($user && !isset($user['name'])) {
            $user['name'] = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
        }

        return $user;
    }

    /**
     * Map user details to a list of items.
     * 
     * @param mixed $items Collection or Paginator
     * @param array $mappings Array of local_field => target_field mappings (e.g. ['created_by' => 'creator'])
     * @return mixed
     */
    public function enrichWithUsers($items, array $mappings)
    {
        $users = $this->getAllUsers();

        // Handle both simple Collections and Paginators
        $collection = $items instanceof \Illuminate\Pagination\LengthAwarePaginator 
            ? $items->getCollection() 
            : $items;

        $collection->transform(function ($item) use ($users, $mappings) {
            foreach ($mappings as $localField => $targetField) {
                if (isset($item->$localField)) {
                    $userId = $item->$localField;
                    $user = $users->get($userId);
                    
                    \Log::info('Enriching user field', [
                        'local_field' => $localField,
                        'target_field' => $targetField,
                        'user_id' => $userId,
                        'user_found' => $user !== null,
                        'total_users_available' => $users->count()
                    ]);
                    
                    // Return only specific fields instead of the entire user object
                    if ($user) {
                        $item->$targetField = [
                            'id' => $user['id'] ?? null,
                            'firstname' => $user['firstname'] ?? null,
                            'lastname' => $user['lastname'] ?? null,
                            'email' => $user['email'] ?? null,
                            'name' => trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')),
                        ];
                    } else {
                        $item->$targetField = null;
                    }
                }
            }
            return $item;
        });

        if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $items->setCollection($collection);
        }

        return $items;
    }
}
