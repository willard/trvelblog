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

        $posts = Post::query()
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
            ->get([
                'id',
                'title',
                'slug',
                'content',
                'location_name',
                'latitude',
                'longitude',
                'travel_date',
                'category',
                'published_at',
            ]);

        return Inertia::render('Home', [
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
