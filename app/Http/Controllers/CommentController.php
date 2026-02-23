<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    /**
     * Store a new guest comment.
     */
    public function store(StoreCommentRequest $request): RedirectResponse
    {
        Comment::create($request->validated());

        return back()->with('success', 'Comment submitted, awaiting approval.');
    }
}
