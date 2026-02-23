<?php

namespace Tests\Feature;

use App\Enums\PostCategory;
use App\Models\Post;
use App\Models\PostPhoto;
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

    public function test_post_show_includes_photos_with_correct_shape(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create();
        PostPhoto::factory()->cover()->for($post)->create();
        PostPhoto::factory()->for($post)->count(2)->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('posts/Show')
            ->has('post.photos', 3)
            ->has('post.photos.0', fn ($photo) => $photo
                ->has('id')
                ->has('path')
                ->has('is_cover')
                ->has('order')
                ->etc()
            )
        );
    }

    public function test_post_show_with_no_photos_returns_empty_photos_array(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('posts/Show')
            ->has('post.photos', 0)
        );
    }

    public function test_post_show_includes_related_posts_from_same_category(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->withCategory(PostCategory::Beach)->create();
        Post::factory()->count(2)->for($user)->published()->withCategory(PostCategory::Beach)->create();
        Post::factory()->for($user)->published()->withCategory(PostCategory::Mountain)->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertInertia(fn ($page) => $page
            ->component('posts/Show')
            ->has('relatedPosts', 2)
        );
    }

    public function test_related_posts_exclude_current_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->withCategory(PostCategory::Beach)->create();
        Post::factory()->for($user)->published()->withCategory(PostCategory::Beach)->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertInertia(fn ($page) => $page
            ->component('posts/Show')
            ->where('relatedPosts', fn ($related) => collect($related)->every(fn ($r) => $r['id'] !== $post->id))
        );
    }

    public function test_related_posts_are_empty_when_no_same_category_posts_exist(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->withCategory(PostCategory::Beach)->create();
        Post::factory()->for($user)->published()->withCategory(PostCategory::Mountain)->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertInertia(fn ($page) => $page
            ->component('posts/Show')
            ->has('relatedPosts', 0)
        );
    }
}
