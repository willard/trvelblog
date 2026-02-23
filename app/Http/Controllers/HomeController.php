<?php

namespace App\Http\Controllers;

use App\Enums\PostCategory;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $category = $request->string('category')->toString() ?: null;
        $search = $request->string('search')->toString() ?: null;

        $columns = [
            'id',
            'title',
            'slug',
            'content',
            'location_name',
            'latitude',
            'longitude',
            'travel_date',
            'category',
            'tags',
            'is_featured',
            'published_at',
        ];

        $featuredPosts = Post::query()
            ->published()
            ->where('is_featured', true)
            ->with('coverPhoto')
            ->latest('published_at')
            ->limit(3)
            ->get($columns);

        $posts = Inertia::scroll(fn () => Post::query()
            ->published()
            ->with('coverPhoto')
            ->when(
                $category !== null && PostCategory::tryFrom($category) !== null,
                fn ($query) => $query->where('category', $category),
            )
            ->when(
                $search !== null,
                fn ($query) => $query->where(fn ($q) => $q
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('location_name', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                ),
            )
            ->latest('published_at')
            ->paginate(12, $columns));

        return Inertia::render('Home', [
            'featuredPosts' => $featuredPosts,
            'posts' => $posts,
            'filters' => [
                'category' => $category,
                'search' => $search,
            ],
            'seo' => [
                'title' => config('app.name').' — Travel Blog',
                'description' => 'Explore travel stories, destinations, and adventures from around the world.',
                'canonical' => route('home'),
                'og_image' => null,
            ],
        ]);
    }
}
