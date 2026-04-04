<?php

namespace Database\Seeders;

use App\Models\InquiryStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InquiryStatusSeeder extends Seeder
{
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
