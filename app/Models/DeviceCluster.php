<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCluster extends Model
{
    protected $fillable = [
        'service_provider_id', 'device_id'
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
