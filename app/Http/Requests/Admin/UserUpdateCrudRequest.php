<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Backpack\PermissionManager\app\Http\Requests\UserUpdateCrudRequest as BaseRequest;

/** Admin users CRUD (update): Permission Manager's rules plus the sign-in username; the email is optional. */
class UserUpdateCrudRequest extends BaseRequest
{
    public function rules()
    {
        $id = $this->get('id') ?? $this->route('id');

        return array_merge(parent::rules(), [
            'username' => User::usernameRules($id),
            'email' => User::emailRules($id),
        ]);
    }
}
