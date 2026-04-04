<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class CipherSweetEloquentUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * Overrides the default provider to use blind index lookups
     * for encrypted fields (e.g., email) instead of plain WHERE clauses.
     *
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
    {
        $credentials = array_filter(
            $credentials,
            fn ($key) => ! str_contains($key, 'password'),
            ARRAY_FILTER_USE_KEY
        );

        if (empty($credentials)) {
            return;
        }

        $model = $this->createModel();
        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } elseif ($value instanceof \Closure) {
                $value($query);
            } elseif ($model instanceof CipherSweetEncrypted && $key === 'email') {
                $query->whereBlind($key, 'email_index', $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }
}
