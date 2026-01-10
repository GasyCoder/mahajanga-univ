<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Redacteur','Validateur']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Redacteur','Validateur']);
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->hasRole('SuperAdmin')) return true;

        if ($user->hasRole('Redacteur')) {
            return $post->author_id === $user->id && in_array($post->status, ['draft','pending'], true);
        }

        if ($user->hasRole('Validateur')) {
            return $post->status === 'pending';
        }

        return false;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->hasRole('SuperAdmin');
    }

    public function submit(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Redacteur'])
            && $post->author_id === $user->id
            && $post->status === 'draft';
    }

    public function approve(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Validateur'])
            && $post->status === 'pending';
    }

    public function reject(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Validateur'])
            && $post->status === 'pending';
    }

    public function archive(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Validateur'])
            && $post->status === 'published';
    }
}
