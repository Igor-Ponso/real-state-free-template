<?php

namespace Database\Seeders;

use App\Models\InquiryStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the inquiry status lookup table.
 *
 * Creates four workflow statuses for property inquiries:
 * New, Read, Replied, and Archived. These track the lifecycle of
 * messages from prospective buyers/renters to agents.
 */
class InquiryStatusSeeder extends Seeder
{
    /**
     * Create inquiry status records using firstOrCreate for idempotency.
     *
     * Statuses are inserted in workflow order (sort_order = array index):
     * 0=New (unread), 1=Read (seen by agent), 2=Replied (agent responded),
     * 3=Archived (conversation closed).
     */
    public function run(): void
    {
        $statuses = ['New', 'Read', 'Replied', 'Archived'];

        foreach ($statuses as $index => $status) {
            InquiryStatus::firstOrCreate(
                ['slug' => Str::slug($status)],
                ['name' => $status, 'sort_order' => $index, 'is_active' => true],
            );
        }
    }
}
