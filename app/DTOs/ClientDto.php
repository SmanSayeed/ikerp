<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class ClientDto extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $role,
        public ?string $address = null,
        public ?string $phone = null,
        public string $client_type,
        public ?string $payment_due_date = null,
        public ?float $vat_slab = null,
        public ?string $gbs_information = null,
        public bool $is_vip = false,
        public ?float $vip_discount = null,
        public ?int $parent_client_id = null,
        public bool $status = true
    ) {}
}
