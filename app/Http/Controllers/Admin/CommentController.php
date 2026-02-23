<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommentController extends Controller
{
    /**
     * Display a listing of comments.
     */
    public function index(Request $request): Response
    {
        $filter = $request->input('filter', 'all');

        $comments = Comment::query()
            ->with('post:id,title,slug')
            ->when($filter === 'pending', fn ($q) => $q->where('is_approved', false))
            ->when($filter === 'approved', fn ($q) => $q->where('is_approved', true))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/comments/Index', [
            'comments' => $comments,
            'filter' => $filter,
        ]);
    }

    /**
     * Approve a comment.
     */
    public function approve(Comment $comment): RedirectResponse
    {
        $comment->update(['is_approved' => true]);

        return back()->with('success', 'Comment approved.');
    }

    /**
     * Reject (unapprove) a comment.
     */
    public function reject(Comment $comment): RedirectResponse
    {
        $comment->update(['is_approved' => false]);

        return back()->with('success', 'Comment rejected.');
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
