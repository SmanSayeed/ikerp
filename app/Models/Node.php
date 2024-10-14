<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'meshid',
        'nodeid',
        'client_remotik_id',
        'is_child_node',
        'child_client_remotik_id',
        'node_name',
        'mesh_name'
    ];
}
