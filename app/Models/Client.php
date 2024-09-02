<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'email', 'address', 'phone', 'payment_due_date', 'vat_slab', 'gbs_information', 'is_vip', 'vip_discount', 'email_verified_at', 'status', 'parent_client_id', 'user_id', 'type'
    ];

    protected $casts = [
           'is_vip' => 'boolean',
           'status' => 'boolean',
           'email_verified_at' => 'datetime',
       ];

       public function parentClient()
       {
           return $this->belongsTo(Client::class, 'parent_client_id');
       }

       public function childrenClients()
       {
           return $this->hasMany(Client::class, 'parent_client_id');
       }

       public function user()
       {
           return $this->belongsTo(User::class);
       }

       public function deviceMappings()
       {
           return $this->hasMany(ClientDeviceMapping::class);
       }

       public function invoices()
       {
           return $this->hasMany(Invoice::class);
       }
   }

