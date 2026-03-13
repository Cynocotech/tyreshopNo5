<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Palmers Green', 'Southgate', 'Enfield', 'Edmonton', 'Winchmore Hill',
            'Oakwood', 'Cockfosters', 'Bounds Green', 'Wood Green', 'Tottenham',
            'Arnos Grove', 'New Southgate', 'East Barnet', 'Hadley Wood', 'Crews Hill',
            'Grange Park', 'Chase Side', 'Bush Hill Park', 'Bowes Park', 'Friern Barnet',
            'Muswell Hill', 'Highgate', 'Crouch End', 'Stoke Newington', 'Tottenham Hale',
            'Walthamstow', 'Leyton', 'Leytonstone', 'Hackney', 'Clapton', 'Dalston', 'Chingford',
        ];
        foreach ($names as $i => $name) {
            Area::updateOrCreate(
                ['name' => $name],
                ['sort_order' => $i]
            );
        }
    }
}
