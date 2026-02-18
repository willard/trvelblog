<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the home page with published travel posts.
     */
    public function __invoke(): Response
    {
        $posts = Post::query()
            ->published()
            ->with('coverPhoto')
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
        ]);
    }
}
