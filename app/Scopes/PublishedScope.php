<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Automatically filters properties to only show published listings.
 *
 * Applied globally via #[ScopedBy] on the Property model. This prevents
 * draft or unpublished properties from leaking to public-facing pages.
 *
 * Admin controllers bypass this scope using:
 *   Property::withoutGlobalScope(PublishedScope::class)
 */
class PublishedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->qualifyColumn('is_published'), true);
    }
}
