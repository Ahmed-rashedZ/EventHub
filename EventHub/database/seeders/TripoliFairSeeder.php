<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venue;

class TripoliFairSeeder extends Seeder
{
    public function run()
    {
        Venue::updateOrCreate(
            ['name' => 'معرض طرابلس الدولي'],
            [
                'location' => 'طرابلس - شارع عمر المختار',
                'capacity' => 10000,
                'status' => 'available',
                'morning_start' => '08:00',
                'morning_end' => '14:00',
                'evening_start' => '16:00',
                'evening_end' => '22:00'
            ]
        );
    }
}
