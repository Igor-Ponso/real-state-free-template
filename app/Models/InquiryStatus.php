<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Lookup table for inquiry workflow statuses (e.g., new, read, replied, closed).
 *
 * Admin-manageable via panel without deploys. Scopes on slug allow
 * code-level filtering while keeping display names editable.
 *
 * @property int $id
 * @property string $name Human-readable status label
 * @property string $slug Unique machine-friendly identifier
 * @property int $sort_order Display ordering position
 * @property bool $is_active Whether this status is currently available
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Inquiry> $inquiries
 */
#[Fillable(['name', 'slug', 'sort_order', 'is_active'])]
class InquiryStatus extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Inquiries currently in this status.
     *
     * @return HasMany<Inquiry, $this>
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * Scope to only active (available) inquiry statuses.
     *
     * @param  Builder<InquiryStatus>  $query
     * @return Builder<InquiryStatus>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order statuses by their sort_order column.
     *
     * @param  Builder<InquiryStatus>  $query
     * @return Builder<InquiryStatus>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
