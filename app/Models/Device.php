<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'name', 'description', 'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function deviceClusters()
    {
        return $this->hasMany(DeviceCluster::class);
    }
}
