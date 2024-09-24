<?php
// app/Models/SqliteUser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SqliteModelMain extends Model
{
    protected $connection = 'sqlite'; // Use the SQLite connection
    protected $table = 'main'; // Your table name that is in SQLite

    protected $casts = [
        'doc' => 'array',
    ];
}
