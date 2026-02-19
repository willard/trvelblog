<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    // --- HomeController SEO ---

    public function test_home_page_includes_seo_prop(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('seo', fn ($seo) => $seo
                ->has('title')
                ->has('description')
                ->has('canonical')
                ->has('og_image')
                ->etc()
            )
        );
    }

    public function test_home_page_seo_canonical_matches_home_route(): void
    {
        $response = $this->get(route('home'));

        $response->assertInertia(fn ($page) => $page
            ->where('seo.canonical', route('home'))
        );
    }

    // --- MapController SEO ---

    public function test_map_page_includes_seo_prop(): void
    {
        $response = $this->get(route('map'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Map')
            ->has('seo', fn ($seo) => $seo
                ->has('title')
                ->has('description')
                ->has('canonical')
                ->has('og_image')
                ->etc()
            )
        );
    }

    public function test_map_page_seo_canonical_matches_map_route(): void
    {
        $response = $this->get(route('map'));

        $response->assertInertia(fn ($page) => $page
            ->where('seo.canonical', route('map'))
        );
    }

    // --- PostController SEO ---

    public function test_post_show_includes_seo_prop(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('posts/Show')
            ->has('seo', fn ($seo) => $seo
                ->has('title')
                ->has('description')
                ->has('canonical')
                ->has('og_image')
                ->etc()
            )
        );
    }

    public function test_post_show_seo_title_contains_post_title(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create(['title' => 'My Great Adventure']);

        $response = $this->get(route('posts.show', $post));

        $response->assertInertia(fn ($page) => $page
            ->where('seo.title', 'My Great Adventure — '.config('app.name'))
        );
    }

    public function test_post_show_seo_description_is_truncated_content(): void
    {
        $user = User::factory()->create();
        $longContent = str_repeat('This is content. ', 30);
        $post = Post::factory()->for($user)->published()->create(['content' => $longContent]);

        $response = $this->get(route('posts.show', $post));

        $response->assertInertia(fn ($page) => $page
            ->where('seo.description', Str::limit(strip_tags($longContent), 160))
        );
    }

    public function test_post_show_seo_canonical_matches_post_route(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertInertia(fn ($page) => $page
            ->where('seo.canonical', route('posts.show', $post))
        );
    }

    public function test_post_show_seo_og_image_contains_cover_photo_path(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create();
        PostPhoto::factory()->cover()->for($post)->create(['path' => 'posts/cover.jpg']);

        $response = $this->get(route('posts.show', $post));

        $response->assertInertia(fn ($page) => $page
            ->where('seo.og_image', '/storage/posts/cover.jpg')
        );
    }

    public function test_post_show_seo_og_image_is_null_when_no_cover_photo(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertInertia(fn ($page) => $page
            ->where('seo.og_image', null)
        );
    }

    // --- SitemapController ---

    public function test_sitemap_returns_xml_response(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_contains_published_posts(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee(route('posts.show', $post->slug), false);
    }

    public function test_sitemap_does_not_contain_draft_posts(): void
    {
        $user = User::factory()->create();
        $draft = Post::factory()->for($user)->draft()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertDontSee(route('posts.show', $draft->slug), false);
    }

    public function test_sitemap_contains_static_urls(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee(route('home'), false);
        $response->assertSee(route('map'), false);
    }

    // --- Dynamic robots.txt ---

    public function test_robots_txt_returns_text_response(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function test_robots_txt_contains_sitemap_url(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee(url('/sitemap.xml'), false);
    }

    public function test_robots_txt_disallows_admin_paths(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Disallow: /admin', false);
        $response->assertSee('Disallow: /dashboard', false);
    }
}
