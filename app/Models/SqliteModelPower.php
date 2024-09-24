<?php
// app/Models/SqliteUser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SqliteModelPower extends Model
{
    protected $connection = 'sqlite'; // Use the SQLite connection
    protected $table = 'power'; // Your table name that is in SQLite

    protected $casts = [
        'doc' => 'array',
    ];
}
