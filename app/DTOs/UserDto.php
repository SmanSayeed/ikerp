<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class UserDto extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $role,
        public bool $status = true
    ) {
        // Optionally, you can handle password encryption here if needed
    }
}
