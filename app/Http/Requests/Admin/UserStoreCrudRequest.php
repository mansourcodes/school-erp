<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Backpack\PermissionManager\app\Http\Requests\UserStoreCrudRequest as BaseRequest;

/** Admin users CRUD (create): Permission Manager's rules plus the sign-in username; the email is optional. */
class UserStoreCrudRequest extends BaseRequest
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'username' => User::usernameRules(),
            'email' => User::emailRules(),
        ]);
    }
}
