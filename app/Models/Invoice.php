<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'client_is_vip',
        'client_vip_discount',
        'date_range',
        'invoice_status',
        'address',
        'device_usage_details',
        'total_cost',
        'discount',
        'original_cost',
        'due_date',
        'client_remotik_id',
        'invoice_generated_by_user_type',
        'invoice_generated_by_id',
        'for_child_client_remotik_id',
        'seller_id'
    ];

    /**
     * Relationship: Invoice belongs to a client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Mutator: Set device usage details as JSON.
     *
     * @param array $value
     */
    public function setDeviceUsageDetailsAttribute($value)
    {
        $this->attributes['device_usage_details'] = json_encode($value);
    }

    /**
     * Accessor: Get device usage details as an array.
     *
     * @return array
     */
    public function getDeviceUsageDetailsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
