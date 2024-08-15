<?php

namespace App\DTOs;

class UserDto
{
    public $name;
    public $email;
    public $password;
    public $role;
    public $status;

    public function __construct($name, $email, $password, $role, $status = true)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = bcrypt($password);
        $this->role = $role;
        $this->status = $status;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'status' => $this->status,
        ];
    }
}
