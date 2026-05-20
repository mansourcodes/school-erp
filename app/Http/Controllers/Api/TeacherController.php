<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q', '');

        $query = User::role('Teacher');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->select('id', 'name')->paginate(30);
    }

    public function show($id)
    {
        return User::role('Teacher')->findOrFail($id);
    }
}
