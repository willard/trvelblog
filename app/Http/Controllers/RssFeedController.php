<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class RssFeedController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::query()
            ->published()
            ->with('coverPhoto')
            ->latest('published_at')
            ->limit(20)
            ->get(['id', 'title', 'slug', 'content', 'category', 'published_at', 'updated_at']);

        $posts->each(function (Post $post): void {
            $post->excerpt = Str::limit(strip_tags($post->content), 200);
        });

        $content = view('feed', [
            'posts' => $posts,
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
