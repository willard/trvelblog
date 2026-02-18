<?php

namespace App\Http\Controllers;

use App\Enums\PostStatus;
use App\Models\Post;
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

        $post->load('photos');

        return Inertia::render('posts/Show', [
            'post' => $post,
        ]);
    }
}
