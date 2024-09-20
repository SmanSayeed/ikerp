<?php

namespace App\Http\Controllers;

use App\Models\SqliteUser;
use Illuminate\Http\Request;

class SqliteController extends Controller
{
    public function index()
    {
        // Fetch all data from the users table in SQLite
        $users = SqliteUser::all();

        return response()->json($users);
    }
}
