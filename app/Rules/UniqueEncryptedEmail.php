<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueEncryptedEmail implements ValidationRule
{
    public function __construct(
        private ?int $ignoreUserId = null,
    ) {}

    /**
     * Validate that the email is unique using blind index lookup.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = User::whereBlind('email', 'email_index', $value);

        if ($this->ignoreUserId !== null) {
            $query->where('id', '!=', $this->ignoreUserId);
        }

        if ($query->exists()) {
            $fail('The :attribute has already been taken.');
        }
    }
}
