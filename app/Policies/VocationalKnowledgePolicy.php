<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VocationalKnowledge;
use Illuminate\Auth\Access\HandlesAuthorization;

class VocationalKnowledgePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VocationalKnowledge');
    }

    public function view(AuthUser $authUser, VocationalKnowledge $vocationalKnowledge): bool
    {
        return $authUser->can('View:VocationalKnowledge');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VocationalKnowledge');
    }

    public function update(AuthUser $authUser, VocationalKnowledge $vocationalKnowledge): bool
    {
        return $authUser->can('Update:VocationalKnowledge');
    }

    public function delete(AuthUser $authUser, VocationalKnowledge $vocationalKnowledge): bool
    {
        return $authUser->can('Delete:VocationalKnowledge');
    }

    public function restore(AuthUser $authUser, VocationalKnowledge $vocationalKnowledge): bool
    {
        return $authUser->can('Restore:VocationalKnowledge');
    }

}