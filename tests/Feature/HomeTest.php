<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_can_be_rendered(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_home_page_displays_published_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->for($user)->published()->create();
        Post::factory()->count(2)->for($user)->draft()->create();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('posts', 3)
        );
    }

    public function test_home_page_shows_empty_state_when_no_published_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->for($user)->draft()->create();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('posts', 0)
        );
    }

    public function test_home_page_posts_are_ordered_by_published_at_descending(): void
    {
        $user = User::factory()->create();
        $older = Post::factory()->for($user)->published()->create([
            'published_at' => now()->subDays(5),
        ]);
        $newer = Post::factory()->for($user)->published()->create([
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('posts', 2)
            ->where('posts.0.id', $newer->id)
            ->where('posts.1.id', $older->id)
        );
    }
}
