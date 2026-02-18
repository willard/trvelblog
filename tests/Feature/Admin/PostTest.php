<?php

namespace Tests\Feature\Admin;

use App\Enums\PostCategory;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_posts_index(): void
    {
        $response = $this->get(route('admin.posts.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_posts_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.posts.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/posts/Index')
            ->has('posts')
        );
    }

    public function test_users_see_only_their_own_posts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Post::factory()->count(3)->for($user)->create();
        Post::factory()->count(2)->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('admin.posts.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/posts/Index')
            ->where('posts.total', 3)
        );
    }

    public function test_create_page_loads_with_categories_and_statuses(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.posts.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/posts/Create')
            ->has('categories', count(PostCategory::cases()))
            ->has('statuses', count(PostStatus::cases()))
        );
    }

    public function test_user_can_store_a_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'My Beach Vacation',
            'content' => 'This is a wonderful story about my beach vacation experience.',
            'location_name' => 'Bali, Indonesia',
            'latitude' => '-8.3405389',
            'longitude' => '115.0919509',
            'travel_date' => '2025-06-15',
            'category' => PostCategory::Beach->value,
            'status' => PostStatus::Draft->value,
        ]);

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'My Beach Vacation',
            'location_name' => 'Bali, Indonesia',
            'category' => PostCategory::Beach->value,
            'status' => PostStatus::Draft->value,
        ]);
    }

    public function test_user_can_store_post_with_photos(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'Mountain Adventure',
            'content' => 'A thrilling adventure through the mountains with breathtaking views.',
            'photos' => [
                UploadedFile::fake()->image('mountain1.jpg'),
                UploadedFile::fake()->image('mountain2.jpg'),
                UploadedFile::fake()->image('mountain3.jpg'),
            ],
            'cover_index' => 1,
            'location_name' => 'Swiss Alps, Switzerland',
            'latitude' => '46.8182',
            'longitude' => '8.2275',
            'travel_date' => '2025-07-20',
            'category' => PostCategory::Mountain->value,
            'status' => PostStatus::Published->value,
        ]);

        $response->assertRedirect(route('admin.posts.index'));

        $post = Post::where('title', 'Mountain Adventure')->first();
        $this->assertCount(3, $post->photos);
        $this->assertTrue($post->photos[1]->is_cover);
        $this->assertFalse($post->photos[0]->is_cover);
        $this->assertFalse($post->photos[2]->is_cover);

        foreach ($post->photos as $photo) {
            Storage::disk('public')->assertExists($photo->path);
        }
    }

    public function test_first_photo_is_cover_by_default(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'Default Cover Test',
            'content' => 'Testing that the first photo becomes the cover by default.',
            'photos' => [
                UploadedFile::fake()->image('photo1.jpg'),
                UploadedFile::fake()->image('photo2.jpg'),
            ],
            'cover_index' => 0,
            'location_name' => 'Tokyo, Japan',
            'latitude' => '35.6762',
            'longitude' => '139.6503',
            'travel_date' => '2025-06-01',
            'category' => PostCategory::City->value,
            'status' => PostStatus::Draft->value,
        ]);

        $post = Post::where('title', 'Default Cover Test')->first();
        $this->assertTrue($post->photos->first()->is_cover);
        $this->assertNotNull($post->coverPhoto);
    }

    public function test_published_status_sets_published_at(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'Published Post',
            'content' => 'This post should be published immediately upon creation.',
            'location_name' => 'Paris, France',
            'latitude' => '48.8566',
            'longitude' => '2.3522',
            'travel_date' => '2025-01-10',
            'category' => PostCategory::City->value,
            'status' => PostStatus::Published->value,
        ]);

        $post = Post::where('title', 'Published Post')->first();
        $this->assertNotNull($post->published_at);
    }

    public function test_draft_status_leaves_published_at_null(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'Draft Post',
            'content' => 'This post is saved as a draft for future publishing.',
            'location_name' => 'Berlin, Germany',
            'latitude' => '52.52',
            'longitude' => '13.405',
            'travel_date' => '2025-02-15',
            'category' => PostCategory::Cultural->value,
            'status' => PostStatus::Draft->value,
        ]);

        $post = Post::where('title', 'Draft Post')->first();
        $this->assertNull($post->published_at);
    }

    public function test_validation_errors_missing_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'content' => 'Some content that is long enough to pass.',
            'location_name' => 'Tokyo, Japan',
            'latitude' => '35.6762',
            'longitude' => '139.6503',
            'travel_date' => '2025-05-01',
            'category' => PostCategory::Food->value,
            'status' => PostStatus::Draft->value,
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_validation_errors_missing_content(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'A Valid Title',
            'location_name' => 'Tokyo, Japan',
            'latitude' => '35.6762',
            'longitude' => '139.6503',
            'travel_date' => '2025-05-01',
            'category' => PostCategory::Food->value,
            'status' => PostStatus::Draft->value,
        ]);

        $response->assertSessionHasErrors('content');
    }

    public function test_validation_errors_missing_location_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'A Valid Title',
            'content' => 'Some content that is long enough to pass validation.',
            'latitude' => '35.6762',
            'longitude' => '139.6503',
            'travel_date' => '2025-05-01',
            'category' => PostCategory::Food->value,
            'status' => PostStatus::Draft->value,
        ]);

        $response->assertSessionHasErrors('location_name');
    }

    public function test_validation_errors_invalid_latitude(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'A Valid Title',
            'content' => 'Some content that is long enough to pass validation.',
            'location_name' => 'Somewhere',
            'latitude' => '91',
            'longitude' => '139.6503',
            'travel_date' => '2025-05-01',
            'category' => PostCategory::Food->value,
            'status' => PostStatus::Draft->value,
        ]);

        $response->assertSessionHasErrors('latitude');
    }

    public function test_validation_errors_invalid_longitude(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'A Valid Title',
            'content' => 'Some content that is long enough to pass validation.',
            'location_name' => 'Somewhere',
            'latitude' => '35.6762',
            'longitude' => '181',
            'travel_date' => '2025-05-01',
            'category' => PostCategory::Food->value,
            'status' => PostStatus::Draft->value,
        ]);

        $response->assertSessionHasErrors('longitude');
    }

    public function test_validation_errors_invalid_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'A Valid Title',
            'content' => 'Some content that is long enough to pass validation.',
            'location_name' => 'Somewhere',
            'latitude' => '35.6762',
            'longitude' => '139.6503',
            'travel_date' => '2025-05-01',
            'category' => 'invalid_category',
            'status' => PostStatus::Draft->value,
        ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_validation_errors_invalid_status(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'A Valid Title',
            'content' => 'Some content that is long enough to pass validation.',
            'location_name' => 'Somewhere',
            'latitude' => '35.6762',
            'longitude' => '139.6503',
            'travel_date' => '2025-05-01',
            'category' => PostCategory::Food->value,
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_validation_errors_future_travel_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'A Valid Title',
            'content' => 'Some content that is long enough to pass validation.',
            'location_name' => 'Somewhere',
            'latitude' => '35.6762',
            'longitude' => '139.6503',
            'travel_date' => now()->addYear()->format('Y-m-d'),
            'category' => PostCategory::Food->value,
            'status' => PostStatus::Draft->value,
        ]);

        $response->assertSessionHasErrors('travel_date');
    }

    public function test_max_10_photos_validation(): void
    {
        $user = User::factory()->create();

        $photos = [];
        for ($i = 0; $i < 11; $i++) {
            $photos[] = UploadedFile::fake()->image("photo{$i}.jpg");
        }

        $response = $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'Too Many Photos',
            'content' => 'Testing that more than 10 photos is rejected.',
            'photos' => $photos,
            'location_name' => 'Somewhere',
            'latitude' => '35.6762',
            'longitude' => '139.6503',
            'travel_date' => '2025-05-01',
            'category' => PostCategory::Food->value,
            'status' => PostStatus::Draft->value,
        ]);

        $response->assertSessionHasErrors('photos');
    }

    public function test_user_can_view_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('admin.posts.show', $post));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/posts/Show')
            ->has('post')
        );
    }

    public function test_user_cannot_view_another_users_post(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('admin.posts.show', $post));

        $response->assertForbidden();
    }

    public function test_edit_page_loads_with_post_data(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        PostPhoto::factory()->cover()->for($post)->create();
        PostPhoto::factory()->for($post)->create();

        $response = $this->actingAs($user)->get(route('admin.posts.edit', $post));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/posts/Edit')
            ->has('post')
            ->has('post.photos', 2)
            ->where('post.travel_date', $post->travel_date->toJSON())
            ->has('categories')
            ->has('statuses')
        );
    }

    public function test_user_can_update_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('admin.posts.update', $post), [
            'title' => 'Updated Title',
            'content' => 'Updated content that is long enough to pass validation.',
            'location_name' => 'Updated Location',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'travel_date' => '2025-03-15',
            'category' => PostCategory::City->value,
            'status' => PostStatus::Published->value,
        ]);

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'location_name' => 'Updated Location',
        ]);
    }

    public function test_user_can_add_photos_on_update(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $existingPhoto = PostPhoto::factory()->cover()->for($post)->create();

        $response = $this->actingAs($user)->put(route('admin.posts.update', $post), [
            'title' => $post->title,
            'content' => $post->content,
            'photos' => [
                UploadedFile::fake()->image('new-photo.jpg'),
            ],
            'existing_photos' => [$existingPhoto->id],
            'cover_photo_id' => $existingPhoto->id,
            'location_name' => $post->location_name,
            'latitude' => $post->latitude,
            'longitude' => $post->longitude,
            'travel_date' => $post->travel_date->format('Y-m-d'),
            'category' => $post->category->value,
            'status' => $post->status->value,
        ]);

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertCount(2, $post->fresh()->photos);
    }

    public function test_user_can_remove_photos_on_update(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $keepPhoto = PostPhoto::factory()->cover()->for($post)->create([
            'path' => 'posts/keep.jpg',
        ]);
        $removePhoto = PostPhoto::factory()->for($post)->create([
            'path' => 'posts/remove.jpg',
        ]);

        Storage::disk('public')->put('posts/keep.jpg', 'content');
        Storage::disk('public')->put('posts/remove.jpg', 'content');

        $response = $this->actingAs($user)->put(route('admin.posts.update', $post), [
            'title' => $post->title,
            'content' => $post->content,
            'existing_photos' => [$keepPhoto->id],
            'cover_photo_id' => $keepPhoto->id,
            'location_name' => $post->location_name,
            'latitude' => $post->latitude,
            'longitude' => $post->longitude,
            'travel_date' => $post->travel_date->format('Y-m-d'),
            'category' => $post->category->value,
            'status' => $post->status->value,
        ]);

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertCount(1, $post->fresh()->photos);
        Storage::disk('public')->assertMissing('posts/remove.jpg');
        Storage::disk('public')->assertExists('posts/keep.jpg');
    }

    public function test_user_can_change_cover_photo(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $photo1 = PostPhoto::factory()->cover()->for($post)->create(['order' => 0]);
        $photo2 = PostPhoto::factory()->for($post)->create(['order' => 1]);

        $response = $this->actingAs($user)->put(route('admin.posts.update', $post), [
            'title' => $post->title,
            'content' => $post->content,
            'existing_photos' => [$photo1->id, $photo2->id],
            'cover_photo_id' => $photo2->id,
            'location_name' => $post->location_name,
            'latitude' => $post->latitude,
            'longitude' => $post->longitude,
            'travel_date' => $post->travel_date->format('Y-m-d'),
            'category' => $post->category->value,
            'status' => $post->status->value,
        ]);

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertTrue($photo2->fresh()->is_cover);
        $this->assertFalse($photo1->fresh()->is_cover);
    }

    public function test_user_can_delete_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('admin.posts.destroy', $post));

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_deleting_post_removes_all_photos(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $photo1 = PostPhoto::factory()->cover()->for($post)->create([
            'path' => 'posts/photo1.jpg',
        ]);
        $photo2 = PostPhoto::factory()->for($post)->create([
            'path' => 'posts/photo2.jpg',
        ]);

        Storage::disk('public')->put('posts/photo1.jpg', 'content');
        Storage::disk('public')->put('posts/photo2.jpg', 'content');

        $this->actingAs($user)->delete(route('admin.posts.destroy', $post));

        Storage::disk('public')->assertMissing('posts/photo1.jpg');
        Storage::disk('public')->assertMissing('posts/photo2.jpg');
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('post_photos', ['post_id' => $post->id]);
    }

    public function test_slug_is_generated_from_title(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.posts.store'), [
            'title' => 'My Amazing Beach Trip',
            'content' => 'This is a wonderful story about my amazing beach trip.',
            'location_name' => 'Maldives',
            'latitude' => '3.2028',
            'longitude' => '73.2207',
            'travel_date' => '2025-08-01',
            'category' => PostCategory::Beach->value,
            'status' => PostStatus::Draft->value,
        ]);

        $post = Post::where('title', 'My Amazing Beach Trip')->first();
        $this->assertNotNull($post);
        $this->assertEquals('my-amazing-beach-trip', $post->slug);
    }
}
