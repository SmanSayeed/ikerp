<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class ClientRegisterDto extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $address = null,
        public ?string $client_remotik_id = null,
        public ?string $phone = null,
        public ?string $payment_due_date = null,
        public ?float $vat_slab = null,
        public ?string $gbs_information = null,
        public bool $is_vip = false,
        public bool $is_seller = false,
        public ?float $vip_discount = null,
        public ?string $parent_client_id = null,
        public bool $is_parent = true,
        public bool $is_child=false,
        public bool $status=false
    ) {}
}
