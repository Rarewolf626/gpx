<?php

namespace GPX\Rule;

use Illuminate\Contracts\Validation\Rule;

class AllowedUsername implements Rule
{

    public function passes($attribute, $value)
    {
        $username = sanitize_user($value, true);
        return $username === trim($value);
    }

    public function message()
    {
        return 'Usernames may only contain alphanumeric characters, dashes, underscores, and @.';
    }
}
