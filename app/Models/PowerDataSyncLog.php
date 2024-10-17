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

      /**
     * Get the last synced log.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function getLastSyncedLog()
    {
        return self::orderBy('created_at', 'desc')->first();
    }

    /**
     * Get all logs ordered by created_at in descending order.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllLogs()
    {
        return self::orderBy('created_at', 'desc')->get();
    }
}
