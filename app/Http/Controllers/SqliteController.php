<?php

namespace App\Http\Controllers;

use App\Models\SqliteModel;
use Illuminate\Http\Request;

class SqliteController extends Controller
{
    public function index()
    {
        // Fetch all data from the users table in SQLite
        $users = SqliteModel::all();

        return response()->json($users);
    }
}
