<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDeviceMapping extends Model
{
    protected $fillable = [
        'client_id', 'remotik_id', 'device_id', 'service_provider_id'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
