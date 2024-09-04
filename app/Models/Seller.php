<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $fillable = [
        'company_name',
        'company_address',
        'company_logo',
        'company_vat_number',
        'company_kvk_number',
        'client_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
