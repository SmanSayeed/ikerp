<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PowerDataSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_remotik_id',
        'synced_count',
        'status',
        'message',
    ];
}
