<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RssFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_rss_feed_returns_200_with_xml_content_type(): void
    {
        $response = $this->get(route('feed'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
    }

    public function test_rss_feed_contains_valid_rss_structure(): void
    {
        $response = $this->get(route('feed'));

        $response->assertOk();
        $this->assertStringContainsString('<rss version="2.0"', $response->getContent());
        $this->assertStringContainsString('<channel>', $response->getContent());
    }

    public function test_rss_feed_includes_published_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create(['title' => 'My Test Post']);

        $response = $this->get(route('feed'));

        $this->assertStringContainsString('My Test Post', $response->getContent());
    }

    public function test_rss_feed_excludes_draft_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->draft()->create(['title' => 'Draft Post']);

        $response = $this->get(route('feed'));

        $this->assertStringNotContainsString('Draft Post', $response->getContent());
    }

    public function test_rss_feed_limits_to_20_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(25)->for($user)->published()->create();

        $response = $this->get(route('feed'));

        $this->assertEquals(20, substr_count($response->getContent(), '<item>'));
    }
}
