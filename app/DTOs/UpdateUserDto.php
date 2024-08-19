<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class UpdateUserDto extends Data
{
    public function __construct(
        public string $name,
    ) {
        // Optionally, you can handle password encryption here if needed
    }
}
