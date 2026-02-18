<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with stats.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'totalPosts' => $user->posts()->count(),
                'publishedCount' => $user->posts()->where('status', PostStatus::Published)->count(),
                'draftCount' => $user->posts()->where('status', PostStatus::Draft)->count(),
            ],
            'postsByCategory' => $user->posts()
                ->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category'),
            'latestPosts' => $user->posts()
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'slug', 'status', 'category', 'travel_date', 'created_at']),
        ]);
    }
}
