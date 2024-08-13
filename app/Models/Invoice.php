<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'client_id', 'date_range', 'invoice_type', 'invoice_status', 'address', 'device_usage_details', 'total_price'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
