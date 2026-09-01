<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/run-migrations', function () {
    Artisan::call('migrate', ['--force' => true]);
    return 'Migrations output:<br><pre>' . Artisan::output() . '</pre>';
});




Route::get('/docs', function () {
    return view('docs');
});

Route::get('/tester', function () {
    $userService = resolve(\App\Services\ExternalUserService::class);
    try {
        $users = $userService->getAllUsers();
    } catch (\Exception $e) {
        $users = collect();
    }

    $users = $users->sortBy(function($u) {
        $name = trim(($u['firstname'] ?? '') . ' ' . ($u['lastname'] ?? ''));
        return empty($name) ? ($u['name'] ?? '') : $name;
    });

    $isAuthoriserFunc = function ($u) {
        $roleId = null;
        $roleName = '';
        if (isset($u['role'])) {
            if (is_array($u['role'])) {
                $roleName = $u['role']['name'] ?? '';
                $roleId = $u['role']['id'] ?? null;
            } else {
                $roleName = $u['role'];
                $roleId = $u['role'];
            }
        }
        $roleIdAttr = $u['role_id'] ?? null;
        $roleNameUpper = strtoupper($roleName);

        return $roleId == 67 || $roleIdAttr == 67 || in_array($roleNameUpper, ['AUTHORISER', 'ADMIN', 'AUTHORIZER']) || str_contains($roleNameUpper, 'ADMIN') || str_contains($roleNameUpper, 'AUTHORIS') || str_contains($roleNameUpper, 'AUTHORIZ');
    };

    $inputters = $users->filter(function ($u) use ($isAuthoriserFunc) {
        return !$isAuthoriserFunc($u);
    });

    $authorisers = $users->filter(function ($u) use ($isAuthoriserFunc) {
        return $isAuthoriserFunc($u);
    });

    // Add mock system integrator as an authoriser option for local/testing
    $mockAuthoriser = [
        'id' => 999,
        'firstname' => 'System',
        'lastname' => 'Integrator',
        'email' => 'integrator@test.com',
        'role' => 'AUTHORISER',
        'status' => 'active'
    ];

    if (!$authorisers->contains('id', 999)) {
        $authorisers->push($mockAuthoriser);
    }

    // Also make sure we have at least some inputters (fallback to all users if empty)
    if ($inputters->isEmpty()) {
        $inputters = $users;
    }

    return view('tester', compact('inputters', 'authorisers', 'users'));
});

Route::get('/articles/{slug}', function ($slug) {
    $article = App\Models\Article::where('slug', $slug)->first();
    if (!$article) {
        abort(404);
    }
    
    $articleService = resolve(App\Services\ArticleService::class);
    $enriched = $articleService->enrichArticles($article);
    
    return view('article', ['article' => $enriched]);
});

Route::get('/newsletter/unsubscribe', [App\Http\Controllers\NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe')
    ->middleware('signed');


