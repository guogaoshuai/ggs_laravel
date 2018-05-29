<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * [uodate 只有当前用户可修改]
     * @param  User   $currentUser [description]
     * @param  User   $user        [description]
     * @return [type]              [description]
     */
    public function update(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
        
    }
    
    /**
     * [destroy 只有有权限的可执行删除操作]
     * @param  User   $currentUser [description]
     * @param  User   $user        [description]
     * @return [type]              [description]
     */
    public function destroy(User $currentUser, User $user)
    {
        return $currentUser->is_admin && $currentUser->id !== $user->id;
    }
}
