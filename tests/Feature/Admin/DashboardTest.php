<?php

namespace Tests\Feature\Admin;

use App\Enums\PostCategory;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_dashboard_displays_correct_stats(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->published()->for($user)->create();
        Post::factory()->count(2)->draft()->for($user)->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Dashboard')
            ->where('stats.totalPosts', 5)
            ->where('stats.publishedCount', 3)
            ->where('stats.draftCount', 2)
        );
    }

    public function test_dashboard_displays_posts_by_category(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->withCategory(PostCategory::Beach)->for($user)->create();
        Post::factory()->count(1)->withCategory(PostCategory::Mountain)->for($user)->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Dashboard')
            ->has('postsByCategory')
        );
    }

    public function test_dashboard_displays_latest_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(7)->for($user)->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Dashboard')
            ->has('latestPosts', 5)
        );
    }
}
