<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostCategory;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('admin/posts/Index', [
            'posts' => $request->user()
                ->posts()
                ->with('coverPhoto')
                ->latest()
                ->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/posts/Create', [
            'categories' => collect(PostCategory::cases())->map(fn (PostCategory $c) => [
                'value' => $c->value,
                'label' => $c->name,
            ]),
            'statuses' => collect(PostStatus::cases())->map(fn (PostStatus $s) => [
                'value' => $s->value,
                'label' => $s->name,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['slug'] = $this->generateUniqueSlug($validated['title']);

        if ($validated['status'] === PostStatus::Published->value) {
            $validated['published_at'] = now();
        }

        unset($validated['photos'], $validated['cover_index']);

        $post = $request->user()->posts()->create($validated);

        $this->storePhotos($request, $post);

        return to_route('admin.posts.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): Response|RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $post->load('photos');

        return Inertia::render('admin/posts/Show', [
            'post' => $post,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Post $post): Response|RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $post->load('photos');

        return Inertia::render('admin/posts/Edit', [
            'post' => $post,
            'categories' => collect(PostCategory::cases())->map(fn (PostCategory $c) => [
                'value' => $c->value,
                'label' => $c->name,
            ]),
            'statuses' => collect(PostStatus::cases())->map(fn (PostStatus $s) => [
                'value' => $s->value,
                'label' => $s->name,
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validated();

        if ($post->title !== $validated['title']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $post->id);
        }

        if ($validated['status'] === PostStatus::Published->value && ! $post->published_at) {
            $validated['published_at'] = now();
        } elseif ($validated['status'] === PostStatus::Draft->value) {
            $validated['published_at'] = null;
        }

        unset($validated['photos'], $validated['cover_index'], $validated['existing_photos'], $validated['cover_photo_id']);

        $post->update($validated);

        $this->syncPhotos($request, $post);

        return to_route('admin.posts.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post): RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        foreach ($post->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        $post->delete();

        return to_route('admin.posts.index');
    }

    /**
     * Store uploaded photos for a newly created post.
     */
    private function storePhotos(Request $request, Post $post): void
    {
        $files = $request->file('photos', []);
        $coverIndex = (int) $request->input('cover_index', 0);

        foreach ($files as $index => $file) {
            $post->photos()->create([
                'path' => $this->imageOptimizer->store($file, 'posts'),
                'is_cover' => $index === $coverIndex,
                'order' => $index,
            ]);
        }
    }

    /**
     * Sync photos during a post update: remove deleted, add new, update cover.
     */
    private function syncPhotos(Request $request, Post $post): void
    {
        $existingPhotoIds = $request->input('existing_photos', []);
        $coverPhotoId = $request->input('cover_photo_id');
        $coverIndex = $request->input('cover_index');

        // Delete photos that are no longer in existing_photos
        $photosToDelete = $post->photos()->whereNotIn('id', $existingPhotoIds)->get();
        foreach ($photosToDelete as $photo) {
            Storage::disk('public')->delete($photo->path);
            $photo->delete();
        }

        // Reset all cover flags for remaining photos
        $post->photos()->update(['is_cover' => false]);

        // Set cover on existing photo if specified
        if ($coverPhotoId) {
            $post->photos()->where('id', $coverPhotoId)->update(['is_cover' => true]);
        }

        // Store new photos
        $files = $request->file('photos', []);
        $existingCount = count($existingPhotoIds);

        foreach ($files as $index => $file) {
            $isCover = $coverIndex !== null && (int) $coverIndex === $index;

            // If this new photo is the cover, reset any existing cover
            if ($isCover) {
                $post->photos()->update(['is_cover' => false]);
            }

            $post->photos()->create([
                'path' => $this->imageOptimizer->store($file, 'posts'),
                'is_cover' => $isCover,
                'order' => $existingCount + $index,
            ]);
        }

        // Ensure at least one cover photo exists if there are any photos
        if ($post->photos()->exists() && ! $post->photos()->where('is_cover', true)->exists()) {
            $post->photos()->orderBy('order')->first()->update(['is_cover' => true]);
        }
    }

    /**
     * Generate a unique slug from the given title.
     */
    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Post::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
