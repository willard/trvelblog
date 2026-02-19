<?php

namespace App\Http\Controllers;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Display a published post.
     */
    public function show(Post $post): Response
    {
        abort_unless($post->status === PostStatus::Published, 404);

        $post->load('photos')->loadMissing('coverPhoto');

        return Inertia::render('posts/Show', [
            'post' => $post,
            'seo' => [
                'title' => $post->title.' — '.config('app.name'),
                'description' => Str::limit(strip_tags($post->content), 160),
                'canonical' => route('posts.show', $post),
                'og_image' => $post->coverPhoto ? '/storage/'.$post->coverPhoto->path : null,
            ],
        ]);
    }
}
