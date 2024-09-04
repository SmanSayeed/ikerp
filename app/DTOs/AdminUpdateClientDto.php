<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class AdminUpdateClientDto extends Data
{
    public function __construct(
        public string $name,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $password = null,
        public ?bool $is_seller = null,
        public ?\DateTime $payment_due_date = null,
        public ?float $vat_slab = null,
        public ?string $gbs_information = null,
        public ?bool $is_vip = null,
        public ?float $vip_discount = null,
        public ?bool $status = null,
        public ?\DateTime $email_verified_at = null,
    ) {
        // Optionally, you can handle password encryption here if needed
    }
}
