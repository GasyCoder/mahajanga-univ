<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Redacteur','Validateur']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Redacteur','Validateur']);
    }

    public function update(User $user, Document $doc): bool
    {
        if ($user->hasRole('SuperAdmin')) return true;

        if ($user->hasRole('Redacteur')) {
            return $doc->created_by === $user->id && in_array($doc->status, ['draft','pending'], true);
        }

        if ($user->hasRole('Validateur')) {
            return $doc->status === 'pending';
        }

        return false;
    }

    public function delete(User $user, Document $doc): bool
    {
        return $user->hasRole('SuperAdmin');
    }

    public function submit(User $user, Document $doc): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Redacteur'])
            && $doc->created_by === $user->id
            && $doc->status === 'draft';
    }

    public function approve(User $user, Document $doc): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Validateur'])
            && $doc->status === 'pending';
    }

    public function reject(User $user, Document $doc): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Validateur'])
            && $doc->status === 'pending';
    }

    public function archive(User $user, Document $doc): bool
    {
        return $user->hasAnyRole(['SuperAdmin','Validateur'])
            && $doc->status === 'published';
    }
}