<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::query()
            ->published()
            ->latest('published_at')
            ->get([
                'id',
                'title',
                'slug',
                'location_name',
                'latitude',
                'longitude',
                'category',
            ]);

        return Inertia::render('Map', [
            'posts' => $posts,
            'seo' => [
                'title' => 'Map — '.config('app.name'),
                'description' => 'Interactive map of all travel destinations.',
                'canonical' => route('map'),
                'og_image' => null,
            ],
        ]);
    }
}
