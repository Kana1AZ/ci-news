<?php

namespace App\Validation;

class IsPasswordStrong{
    public function is_password_strong($password): bool{

        $password = trim($password);
        if(!preg_match('/^(?=.*[\W])(?=.*[A-Za-z])(?=.*[0-9]).{5,45}$/', $password)) {
           return false;
        }
        return true;
    }
}