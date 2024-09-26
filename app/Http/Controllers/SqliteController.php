<?php

namespace App\Http\Controllers;

use App\Models\SqliteModelEventid;
use App\Models\SqliteModelMain;
use App\Models\SqliteModelPower;
use Illuminate\Http\Request;

class SqliteController extends Controller
{
    public function main()
    {
        // Fetch all data from the users table in SQLite
        $users = SqliteModelMain::all();

        return response()->json($users);
    }

    public function power()
    {
        // Fetch all data from the users table in SQLite
        $users = SqliteModelPower::all();

        return response()->json($users);
    }
    public function eventid()
    {
        // Fetch all data from the users table in SQLite
        $users = SqliteModelEventid::all();

        return response()->json($users);
    }
}
