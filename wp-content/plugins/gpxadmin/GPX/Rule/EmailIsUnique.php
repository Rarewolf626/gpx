<?php

namespace GPX\Rule;

use Illuminate\Contracts\Validation\Rule;

class EmailIsUnique implements Rule
{
    protected ?int $user_id;

    public function __construct(int $user_id = null)
    {
        $this->user_id = $user_id;
    }

    public function passes($attribute, $value): bool
    {
        $id = email_exists($value);
        if (!$this->user_id) {
            return $id === false;
        }

        return $this->user_id === $id;
    }

    public function message(): string
    {
        return 'This email address is taken';
    }
}
