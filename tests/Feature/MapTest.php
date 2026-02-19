<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_page_can_be_rendered(): void
    {
        $response = $this->get(route('map'));

        $response->assertOk();
    }

    public function test_map_page_renders_correct_inertia_component(): void
    {
        $response = $this->get(route('map'));

        $response->assertInertia(fn ($page) => $page->component('Map'));
    }

    public function test_map_page_shows_only_published_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->for($user)->published()->create();
        Post::factory()->count(2)->for($user)->draft()->create();

        $response = $this->get(route('map'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Map')
            ->has('posts', 3)
        );
    }

    public function test_map_page_posts_have_required_map_fields(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create();

        $response = $this->get(route('map'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Map')
            ->has('posts', 1)
            ->has('posts.0', fn ($post) => $post
                ->has('id')
                ->has('title')
                ->has('slug')
                ->has('latitude')
                ->has('longitude')
                ->has('location_name')
                ->has('category')
                ->etc()
            )
        );
    }

    public function test_map_page_shows_empty_posts_when_none_published(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->for($user)->draft()->create();

        $response = $this->get(route('map'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Map')
            ->has('posts', 0)
        );
    }
}
