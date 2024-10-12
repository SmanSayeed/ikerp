<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
class Client extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;
    protected $fillable = [
        'name', 'email', 'address', 'phone', 'payment_due_date', 'vat_slab', 'gbs_information', 'is_vip', 'vip_discount', 'email_verified_at', 'status', 'parent_client_id', 'user_id', 'type','password','is_seller','last_synced','client_remotik_id','is_parent','is_child'
    ];

    protected $casts = [
           'is_vip' => 'boolean',
           'status' => 'boolean',
           'email_verified_at' => 'datetime',
       ];

       protected $hidden = [
        'password', 'remember_token',
        ];

       public function parentClient()
       {
           return $this->belongsTo(Client::class, 'parent_client_id');
       }

       public function childrenClients()
       {
           return $this->hasMany(Client::class, 'parent_client_id');
       }

       public function deviceMappings()
       {
           return $this->hasMany(ClientDeviceMapping::class);
       }

       public function invoices()
       {
           return $this->hasMany(Invoice::class);
       }


    public function seller()
    {
        return $this->hasOne(Seller::class);
    }
   }

