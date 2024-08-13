<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    protected $fillable = [
        'company_name', 'company_address', 'company_logo', 'company_vat_number', 'company_kvk_number', 'user_id', 'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deviceClusters()
    {
        return $this->hasMany(DeviceCluster::class);
    }
}
