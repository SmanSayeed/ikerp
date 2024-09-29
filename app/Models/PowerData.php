<?php
// app/Models/PowerData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PowerData extends Model
{
    use HasFactory;

    protected $fillable = ['time', 'nodeid', 'power','client_id'];
}
