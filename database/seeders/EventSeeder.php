<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Event;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = json_decode(file_get_contents(database_path('data/events.json')), true);

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
