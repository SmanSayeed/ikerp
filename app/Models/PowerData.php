<?php
// app/Models/PowerData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PowerData extends Model
{
    use HasFactory;

    protected $fillable = ['remotik_power_id','time', 'nodeid', 'power','node_name','client_remotik_id','is_parent','is_child','child_client_remotik_id'];

    protected $casts = [
        'is_child' => 'boolean',
        'is_parent' => 'boolean'
    ];
}
