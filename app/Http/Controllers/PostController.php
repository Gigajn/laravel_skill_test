<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of published posts.
     * Paginated (20 per page), with user relationship loaded.
     */
    public function index(): JsonResponse
    {
        $posts = Post::published()
            ->with('user')
            ->latest('published_at')
            ->paginate(20);

        return response()->json($posts);
    }

    /**
     * Show the form for creating a new post.
     * Requires authentication.
     */
    public function create(): string
    {
        return 'posts.create';
    }

    /**
     * Store a newly created post in storage.
     * Requires authentication, validates input.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $request->user()->posts()->create($request->validated());

        return redirect()->route('posts.show', $post)->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified post.
     * Returns 404 if post is draft or scheduled.
     */
    public function show(Post $post): JsonResponse
    {
        if (!$post->isPublished()) {
            abort(404);
        }

        $post->load('user');

        return response()->json($post);
    }

    /**
     * Show the form for editing the specified post.
     * Only the author can edit.
     */
    public function edit(Post $post): string
    {
        $this->authorize('update', $post);

        return 'posts.edit';
    }

    /**
     * Update the specified post in storage.
     * Only the author can update, validates input.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        return redirect()->route('posts.show', $post)->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post from storage.
     * Only the author can delete.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }
}
