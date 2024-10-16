<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class UpdateChildClientDto extends Data
{
    public function __construct(
        public string $name,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $phone = null,
        public ?float $vat_slab = null,
        public ?string $gbs_information = null,
    ) {
        // Optionally, you can handle password encryption here if needed
    }
}
