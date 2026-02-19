<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::query()
            ->published()
            ->latest('published_at')
            ->get(['slug', 'updated_at']);

        $content = view('sitemap', [
            'posts' => $posts,
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
