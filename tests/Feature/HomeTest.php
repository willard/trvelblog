<?php

namespace Tests\Feature;

use App\Enums\PostCategory;
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

    public function test_home_page_passes_null_filters_when_no_params_given(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->where('filters.category', null)
            ->where('filters.search', null)
        );
    }

    public function test_home_page_filters_posts_by_category(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->for($user)->published()->withCategory(PostCategory::Beach)->create();
        Post::factory()->count(3)->for($user)->published()->withCategory(PostCategory::Mountain)->create();

        $response = $this->get(route('home', ['category' => 'beach']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('posts', 2)
            ->where('filters.category', 'beach')
        );
    }

    public function test_home_page_passes_category_filter_in_props(): void
    {
        $response = $this->get(route('home', ['category' => 'beach', 'search' => 'paris']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->where('filters.category', 'beach')
            ->where('filters.search', 'paris')
        );
    }

    public function test_home_page_ignores_invalid_category_value(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->for($user)->published()->create();

        $response = $this->get(route('home', ['category' => 'invalid_category']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('posts', 3)
        );
    }

    public function test_home_page_filters_posts_by_search_in_title(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create(['title' => 'Paris Adventure']);
        Post::factory()->for($user)->published()->create(['title' => 'Tokyo Trip']);

        $response = $this->get(route('home', ['search' => 'paris']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('posts', 1)
            ->where('posts.0.title', 'Paris Adventure')
        );
    }

    public function test_home_page_filters_posts_by_search_in_location_name(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create(['location_name' => 'Bali, Indonesia']);
        Post::factory()->for($user)->published()->create(['location_name' => 'Tokyo, Japan']);

        $response = $this->get(route('home', ['search' => 'bali']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('posts', 1)
            ->where('posts.0.location_name', 'Bali, Indonesia')
        );
    }

    public function test_home_page_filters_posts_by_search_in_content(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create([
            'content' => 'Visited the Eiffel Tower and surrounding areas.',
        ]);
        Post::factory()->for($user)->published()->create([
            'content' => 'Cherry blossoms were in full bloom at the park.',
        ]);

        $response = $this->get(route('home', ['search' => 'eiffel']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('posts', 1)
        );
    }

    public function test_home_page_filters_combine_category_and_search(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->withCategory(PostCategory::Beach)->create(['title' => 'Bali Beach']);
        Post::factory()->for($user)->published()->withCategory(PostCategory::Beach)->create(['title' => 'Maldives']);
        Post::factory()->for($user)->published()->withCategory(PostCategory::Mountain)->create(['title' => 'Bali Mountain']);

        $response = $this->get(route('home', ['category' => 'beach', 'search' => 'bali']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('posts', 1)
            ->where('posts.0.title', 'Bali Beach')
        );
    }
}
