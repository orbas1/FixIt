<?php

namespace Database\Seeders;

use App\Enums\BookingEnum;
use App\Models\BookingStatus;
use Illuminate\Database\Seeder;

class BookingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bookingStatus = [
            [
                'name' => BookingEnum::PENDING,
                'system_reserve' => 1,
                'sequence' => '1',
                'hexa_code' => '#FDB448',
            ],
            [
                'name' => BookingEnum::ACCEPTED,
                'system_reserve' => 1,
                'sequence' => '2',
                'hexa_code' => '#48BFFD',
            ],
            [
                'name' => BookingEnum::ASSIGNED,
                'system_reserve' => 1,
                'sequence' => '3',
                'hexa_code' => '#48BFFD',
            ],
            [
                'name' => BookingEnum::ON_THE_WAY,
                'system_reserve' => 1,
                'sequence' => '4',
                'hexa_code' => '#FF7456',
            ],
            // [
            //     'name' => BookingEnum::DECLINE,
            //     'system_reserve' => 1,
            //     'sequence' => '5',
            //     'hexa_code' => '#FF4B4B',
            // ],
            [
                'name' => BookingEnum::CANCEL,
                'system_reserve' => 1,
                'sequence' => '6',
                'hexa_code' => '#FF4B4B',
            ],
            [
                'name' => BookingEnum::ON_GOING,
                'system_reserve' => 1,
                'sequence' => '7',
                'hexa_code' => '#FF7456',
            ],
            [
                'name' => BookingEnum::ON_HOLD,
                'system_reserve' => 1,
                'sequence' => '8',
                'hexa_code' => '#FF1D53',
            ],
            [
                'name' => BookingEnum::START_AGAIN,
                'system_reserve' => 1,
                'sequence' => '9',
                'hexa_code' => '#FF1D53',
            ],
            [
                'name' => BookingEnum::COMPLETED,
                'system_reserve' => 1,
                'sequence' => '10',
                'hexa_code' => '#5465FF',
            ],
            // [
            //     'name' => BookingEnum::PENDING_APPROVAL,
            //     'system_reserve' => 1,
            //     'sequence' => '11',
            //     'hexa_code' => '#5498FF',
            // ],
        ];

        foreach ($bookingStatus as $status) {
            if (! BookingStatus::where('name', $status['name'])->first()) {
                BookingStatus::create([
                    'name' => $status['name'],
                    'system_reserve' => $status['system_reserve'],
                    'sequence' => $status['sequence'],
                    'hexa_code' => $status['hexa_code'],
                ]);
            }
        }
    }
}
