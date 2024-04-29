<?php

namespace App\Validation;

class DateRules {

    public function check_date_is_future(string $date, string &$error = null): bool {
        $today = date('Y-m-d');
        if ($date >= $today) {
            return true;
        } else {
            $error = 'The date must be in the future.';  // Custom error message
            return false;
        }
    }
}
