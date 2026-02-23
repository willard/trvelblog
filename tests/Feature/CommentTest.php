<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_comment_on_published_post(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->post(route('comments.store'), [
            'post_id' => $post->id,
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'content' => 'Great travel post!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment submitted, awaiting approval.');

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'content' => 'Great travel post!',
            'is_approved' => false,
        ]);
    }

    public function test_comment_requires_valid_fields(): void
    {
        $response = $this->post(route('comments.store'), []);

        $response->assertSessionHasErrors(['post_id', 'guest_name', 'guest_email', 'content']);
    }

    public function test_comment_content_has_min_and_max_length(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->post(route('comments.store'), [
            'post_id' => $post->id,
            'guest_name' => 'Jane',
            'guest_email' => 'jane@example.com',
            'content' => 'ab',
        ]);

        $response->assertSessionHasErrors('content');

        $response = $this->post(route('comments.store'), [
            'post_id' => $post->id,
            'guest_name' => 'Jane',
            'guest_email' => 'jane@example.com',
            'content' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors('content');
    }

    public function test_comment_requires_valid_email(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->post(route('comments.store'), [
            'post_id' => $post->id,
            'guest_name' => 'Jane',
            'guest_email' => 'not-an-email',
            'content' => 'Nice post!',
        ]);

        $response->assertSessionHasErrors('guest_email');
    }

    public function test_guest_can_reply_to_existing_comment(): void
    {
        $post = Post::factory()->published()->create();
        $parent = Comment::factory()->for($post)->approved()->create();

        $response = $this->post(route('comments.store'), [
            'post_id' => $post->id,
            'parent_id' => $parent->id,
            'guest_name' => 'Reply User',
            'guest_email' => 'reply@example.com',
            'content' => 'I agree with this!',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'parent_id' => $parent->id,
            'guest_name' => 'Reply User',
        ]);
    }

    public function test_reply_parent_must_belong_to_same_post(): void
    {
        $post1 = Post::factory()->published()->create();
        $post2 = Post::factory()->published()->create();
        $parentOnPost2 = Comment::factory()->for($post2)->approved()->create();

        $response = $this->post(route('comments.store'), [
            'post_id' => $post1->id,
            'parent_id' => $parentOnPost2->id,
            'guest_name' => 'Bad Reply',
            'guest_email' => 'bad@example.com',
            'content' => 'This should fail.',
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_only_approved_comments_appear_on_post_show(): void
    {
        $post = Post::factory()->published()->create();
        Comment::factory()->for($post)->approved()->count(2)->create();
        Comment::factory()->for($post)->create(); // unapproved

        $response = $this->get(route('posts.show', $post));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('posts/Show')
            ->has('comments', 2)
        );
    }

    public function test_replies_nested_under_parent_in_response(): void
    {
        $post = Post::factory()->published()->create();
        $parent = Comment::factory()->for($post)->approved()->create();
        Comment::factory()->for($post)->approved()->reply($parent)->count(2)->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('posts/Show')
            ->has('comments', 1)
            ->has('comments.0.replies', 2)
        );
    }

    public function test_admin_can_approve_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)->patch(route('admin.comments.approve', $comment));

        $response->assertRedirect();
        $this->assertTrue($comment->fresh()->is_approved);
    }

    public function test_admin_can_reject_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->approved()->create();

        $response = $this->actingAs($user)->patch(route('admin.comments.reject', $comment));

        $response->assertRedirect();
        $this->assertFalse($comment->fresh()->is_approved);
    }

    public function test_admin_can_delete_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_guest_cannot_access_admin_comment_routes(): void
    {
        $comment = Comment::factory()->create();

        $this->get(route('admin.comments.index'))->assertRedirect(route('login'));
        $this->patch(route('admin.comments.approve', $comment))->assertRedirect(route('login'));
        $this->patch(route('admin.comments.reject', $comment))->assertRedirect(route('login'));
        $this->delete(route('admin.comments.destroy', $comment))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_comments_index(): void
    {
        $user = User::factory()->create();
        Comment::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('admin.comments.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/comments/Index')
            ->has('comments.data', 3)
        );
    }

    public function test_admin_can_filter_comments_by_status(): void
    {
        $user = User::factory()->create();
        Comment::factory()->approved()->count(2)->create();
        Comment::factory()->count(3)->create(); // pending

        $response = $this->actingAs($user)->get(route('admin.comments.index', ['filter' => 'pending']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/comments/Index')
            ->has('comments.data', 3)
            ->where('filter', 'pending')
        );

        $response = $this->actingAs($user)->get(route('admin.comments.index', ['filter' => 'approved']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/comments/Index')
            ->has('comments.data', 2)
            ->where('filter', 'approved')
        );
    }

    public function test_deleting_parent_comment_cascades_to_replies(): void
    {
        $post = Post::factory()->published()->create();
        $parent = Comment::factory()->for($post)->create();
        $reply = Comment::factory()->for($post)->reply($parent)->create();

        $parent->delete();

        $this->assertDatabaseMissing('comments', ['id' => $parent->id]);
        $this->assertDatabaseMissing('comments', ['id' => $reply->id]);
    }
}
