<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['What servicing options do you offer?', "We provide comprehensive car and van servicing options — from interim checks to full and major services that maintain your vehicle's performance and reliability."],
            ['How often should my car be serviced?', 'Most manufacturers recommend servicing at least once a year or every 10,000–12,000 miles, whichever comes first. Regular servicing helps reduce breakdown risk and prolong vehicle life.'],
            ['Will servicing with you affect my warranty?', 'No — we use parts of equivalent quality to original manufacturer standards, complying with UK block exemption regulations 1400/2002. Always check your warranty details first if unsure.'],
            ['Do you offer collection and delivery?', 'Yes — we offer free same-day collection and delivery for any service and MOT booking within our North London covered areas. Contact us for more information.'],
            ['Can I book online?', 'Absolutely — book using our online system, choosing the date and time that suits you from our live service diary.'],
            ['Do you service vans?', 'Yes — our servicing covers both cars and light vans with professional care and competitive pricing.'],
            ['What areas do you serve?', 'Palmers Green, Arnos Grove, Wood Green, Tottenham, New Southgate, East Barnet, Winchmore Hill, Southgate, Bounds Green, Bowes Park, Friern Barnet, Muswell Hill, Highgate, Crouch End, Stoke Newington, Tottenham Hale, Walthamstow, Leyton, Leytonstone, Hackney, Clapton, Dalston, Chingford and wider North & East London.'],
            ['Is MOT included with servicing?', 'We offer combo pricing — book your MOT with a Full or Major Service and pay just £19 for the MOT, saving at least £31.'],
        ];
        foreach ($items as $i => [$q, $a]) {
            Faq::updateOrCreate(
                ['question' => $q],
                ['answer' => $a, 'sort_order' => $i]
            );
        }
    }
}
