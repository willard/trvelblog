<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_post_can_be_viewed(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('posts/Show')
            ->has('post')
            ->where('post.slug', $post->slug)
        );
    }

    public function test_draft_post_returns_404(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->draft()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertNotFound();
    }

    public function test_nonexistent_post_returns_404(): void
    {
        $response = $this->get('/posts/nonexistent-slug');

        $response->assertNotFound();
    }
}
