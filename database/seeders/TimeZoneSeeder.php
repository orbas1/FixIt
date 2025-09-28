<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timeZones = [
            [
                'code' => 'UTC',
                'name' => 'UTC',
            ],
            [
                'code' => 'Africa/Abidjan',
                'name' => 'Abidjan',
            ],
            [
                'code' => 'Africa/Accra',
                'name' => 'Accra',
            ],
            [
                'code' => 'Africa/Addis_Ababa',
                'name' => 'Addis_Ababa',
            ],
            [
                'code' => 'Africa/Algiers',
                'name' => 'Algiers',
            ],
            [
                'code' => 'Africa/Asmara',
                'name' => 'Asmara',
            ],
            [
                'code' => 'Africa/Bamako',
                'name' => 'Bamako',
            ],
            [
                'code' => 'Africa/Bangui',
                'name' => 'Bangui',
            ],
            [
                'code' => 'Africa/Banjul',
                'name' => 'Banjul',
            ],
            [
                'code' => 'Africa/Bissau',
                'name' => 'Bissau',
            ],
            [
                'code' => 'Africa/Blantyre',
                'name' => 'Blantyre',
            ],
            [
                'code' => 'Africa/Brazzaville',
                'name' => 'Brazzaville',
            ],
            [
                'code' => 'Africa/Bujumbura',
                'name' => 'Bujumbura',
            ],
            [
                'code' => 'Africa/Cairo',
                'name' => 'Cairo',
            ],
            [
                'code' => 'Africa/Casablanca',
                'name' => 'Casablanca',
            ],
            [
                'code' => 'Africa/Ceuta',
                'name' => 'Ceuta',
            ],
            [
                'code' => 'Africa/Conakry',
                'name' => 'Conakry',
            ],
            [
                'code' => 'Africa/Dakar',
                'name' => 'Dakar',
            ],
            [
                'code' => 'Africa/Dar_es_Salaam',
                'name' => 'Dar_es_Salaam',
            ],
            [
                'code' => 'Africa/Djibouti',
                'name' => 'Djibouti',
            ],
            [
                'code' => 'Africa/Douala',
                'name' => 'Douala',
            ],
            [
                'code' => 'Africa/El_Aaiun',
                'name' => 'El_Aaiun',
            ],
            [
                'code' => 'Africa/Freetown',
                'name' => 'Freetown',
            ],
            [
                'code' => 'Africa/Gaborone',
                'name' => 'Gaborone',
            ],
            [
                'code' => 'Africa/Harare',
                'name' => 'Harare',
            ],
            [
                'code' => 'Africa/Johannesburg',
                'name' => 'Johannesburg',
            ],
            [
                'code' => 'Africa/Juba',
                'name' => 'Juba',
            ],
            [
                'code' => 'Africa/Kampala',
                'name' => 'Kampala',
            ],
            [
                'code' => 'Africa/Khartoum',
                'name' => 'Khartoum',
            ],
            [
                'code' => 'Africa/Kigali',
                'name' => 'Kigali',
            ],
            [
                'code' => 'Africa/Kinshasa',
                'name' => 'Kinshasa',
            ],
            [
                'code' => 'Africa/Lagos',
                'name' => 'Lagos',
            ],
            [
                'code' => 'Africa/Libreville',
                'name' => 'Libreville',
            ],
            [
                'code' => 'Africa/Lome',
                'name' => 'Lome',
            ],
            [
                'code' => 'Africa/Luanda',
                'name' => 'Luanda',
            ],
            [
                'code' => 'Africa/Lubumbashi',
                'name' => 'Lubumbashi',
            ],
            [
                'code' => 'Africa/Lusaka',
                'name' => 'Lusaka',
            ],
            [
                'code' => 'Africa/Malabo',
                'name' => 'Malabo',
            ],
            [
                'code' => 'Africa/Maputo',
                'name' => 'Maputo',
            ],
            [
                'code' => 'Africa/Maseru',
                'name' => 'Maseru',
            ],
            [
                'code' => 'Africa/Mbabane',
                'name' => 'Mbabane',
            ],
            [
                'code' => 'Africa/Mogadishu',
                'name' => 'Mogadishu',
            ],
            [
                'code' => 'Africa/Monrovia',
                'name' => 'Monrovia',
            ],
            [
                'code' => 'Africa/Nairobi',
                'name' => 'Nairobi',
            ],
            [
                'code' => 'Africa/Ndjamena',
                'name' => 'Ndjamena',
            ],
            [
                'code' => 'Africa/Niamey',
                'name' => 'Niamey',
            ],
            [
                'code' => 'Africa/Nouakchott',
                'name' => 'Nouakchott',
            ],
            [
                'code' => 'Africa/Ouagadougou',
                'name' => 'Ouagadougou',
            ],
            [
                'code' => 'Africa/Porto-Novo',
                'name' => 'Porto-Novo',
            ],
            [
                'code' => 'Africa/Sao_Tome',
                'name' => 'Sao_Tome',
            ],
            [
                'code' => 'Africa/Tripoli',
                'name' => 'Tripoli',
            ],
            [
                'code' => 'Africa/Tunis',
                'name' => 'Tunis',
            ],
            [
                'code' => 'Africa/Windhoek',
                'name' => 'Windhoek',
            ],
            [
                'code' => 'America/Adak',
                'name' => 'Adak',
            ],
            [
                'code' => 'America/Anchorage',
                'name' => 'Anchorage',
            ],
            [
                'code' => 'America/Anguilla',
                'name' => 'Anguilla',
            ],
            [
                'code' => 'America/Antigua',
                'name' => 'Antigua',
            ],
            [
                'code' => 'America/Araguaina',
                'name' => 'Araguaina',
            ],
            [
                'code' => 'America/Argentina/Buenos_Aires',
                'name' => 'Buenos_Aires',
            ],
            [
                'code' => 'America/Argentina/Catamarca',
                'name' => 'Catamarca',
            ],
            [
                'code' => 'America/Argentina/Cordoba',
                'name' => 'Cordoba',
            ],
            [
                'code' => 'America/Argentina/Jujuy',
                'name' => 'Jujuy',
            ],
            [
                'code' => 'America/Argentina/La_Rioja',
                'name' => 'La_Rioja',
            ],
            [
                'code' => 'America/Argentina/Mendoza',
                'name' => 'Mendoza',
            ],
            [
                'code' => 'America/Argentina/Rio_Gallegos',
                'name' => 'Rio_Gallegos',
            ],
            [
                'code' => 'America/Argentina/Salta',
                'name' => 'Salta',
            ],
            [
                'code' => 'America/Argentina/San_Juan',
                'name' => 'San_Juan',
            ],
            [
                'code' => 'America/Argentina/San_Luis',
                'name' => 'San_Luis',
            ],
            [
                'code' => 'America/Argentina/Tucuman',
                'name' => 'Tucuman',
            ],
            [
                'code' => 'America/Argentina/Ushuaia',
                'name' => 'Ushuaia',
            ],
            [
                'code' => 'America/Aruba',
                'name' => 'Aruba',
            ],
            [
                'code' => 'America/Asuncion',
                'name' => 'Asuncion',
            ],
            [
                'code' => 'America/Atikokan',
                'name' => 'Atikokan',
            ],
            [
                'code' => 'America/Bahia',
                'name' => 'Bahia',
            ],
            [
                'code' => 'America/Bahia_Banderas',
                'name' => 'Bahia_Banderas',
            ],
            [
                'code' => 'America/Barbados',
                'name' => 'Barbados',
            ],
            [
                'code' => 'America/Belem',
                'name' => 'Belem',
            ],
            [
                'code' => 'America/Belize',
                'name' => 'Belize',
            ],
            [
                'code' => 'America/Blanc-Sablon',
                'name' => 'Blanc-Sablon',
            ],
            [
                'code' => 'America/Boa_Vista',
                'name' => 'Boa_Vista',
            ],
            [
                'code' => 'America/Bogota',
                'name' => 'Bogota',
            ],
            [
                'code' => 'America/Boise',
                'name' => 'Boise',
            ],
            [
                'code' => 'America/Cambridge_Bay',
                'name' => 'Cambridge_Bay',
            ],
            [
                'code' => 'America/Campo_Grande',
                'name' => 'Campo_Grande',
            ],
            [
                'code' => 'America/Cancun',
                'name' => 'Cancun',
            ],
            [
                'code' => 'America/Caracas',
                'name' => 'Caracas',
            ],
            [
                'code' => 'America/Cayenne',
                'name' => 'Cayenne',
            ],
            [
                'code' => 'America/Cayman',
                'name' => 'Cayman',
            ],
            [
                'code' => 'America/Chicago',
                'name' => 'Chicago',
            ],
            [
                'code' => 'America/Chihuahua',
                'name' => 'Chihuahua',
            ],
            [
                'code' => 'America/Costa_Rica',
                'name' => 'Costa_Rica',
            ],
            [
                'code' => 'America/Creston',
                'name' => 'Creston',
            ],
            [
                'code' => 'America/Cuiaba',
                'name' => 'Cuiaba',
            ],
            [
                'code' => 'America/Curacao',
                'name' => 'Curacao',
            ],
            [
                'code' => 'America/Danmarkshavn',
                'name' => 'Danmarkshavn',
            ],
            [
                'code' => 'America/Dawson',
                'name' => 'Dawson',
            ],
            [
                'code' => 'America/Dawson_Creek',
                'name' => 'Dawson_Creek',
            ],
            [
                'code' => 'America/Denver',
                'name' => 'Denver',
            ],
            [
                'code' => 'America/Detroit',
                'name' => 'Detroit',
            ],
            [
                'code' => 'America/Dominica',
                'name' => 'Dominica',
            ],
            [
                'code' => 'America/Edmonton',
                'name' => 'Edmonton',
            ],
            [
                'code' => 'America/Eirunepe',
                'name' => 'Eirunepe',
            ],
            [
                'code' => 'America/El_Salvador',
                'name' => 'El_Salvador',
            ],
            [
                'code' => 'America/Fort_Nelson',
                'name' => 'Fort_Nelson',
            ],
            [
                'code' => 'America/Fortaleza',
                'name' => 'Fortaleza',
            ],
            [
                'code' => 'America/Glace_Bay',
                'name' => 'Glace_Bay',
            ],
            [
                'code' => 'America/Goose_Bay',
                'name' => 'Goose_Bay',
            ],
            [
                'code' => 'America/Grand_Turk',
                'name' => 'Grand_Turk',
            ],
            [
                'code' => 'America/Grenada',
                'name' => 'Grenada',
            ],
            [
                'code' => 'America/Guadeloupe',
                'name' => 'Guadeloupe',
            ],
            [
                'code' => 'America/Guatemala',
                'name' => 'Guatemala',
            ],
            [
                'code' => 'America/Guayaquil',
                'name' => 'Guayaquil',
            ],
            [
                'code' => 'America/Guyana',
                'name' => 'Guyana',
            ],
            [
                'code' => 'America/Halifax',
                'name' => 'Halifax',
            ],
            [
                'code' => 'America/Havana',
                'name' => 'Havana',
            ],
            [
                'code' => 'America/Hermosillo',
                'name' => 'Hermosillo',
            ],
            [
                'code' => 'America/Indiana/Indianapolis',
                'name' => 'Indianapolis',
            ],
            [
                'code' => 'America/Indiana/Knox',
                'name' => 'Knox',
            ],
            [
                'code' => 'America/Indiana/Marengo',
                'name' => 'Marengo',
            ],
            [
                'code' => 'America/Indiana/Petersburg',
                'name' => 'Petersburg',
            ],
            [
                'code' => 'America/Indiana/Tell_City',
                'name' => 'Tell_City',
            ],
            [
                'code' => 'America/Indiana/Vevay',
                'name' => 'Vevay',
            ],
            [
                'code' => 'America/Indiana/Vincennes',
                'name' => 'Vincennes',
            ],
            [
                'code' => 'America/Indiana/Winamac',
                'name' => 'Winamac',
            ],
            [
                'code' => 'America/Inuvik',
                'name' => 'Inuvik',
            ],
            [
                'code' => 'America/Iqaluit',
                'name' => 'Iqaluit',
            ],
            [
                'code' => 'America/Jamaica',
                'name' => 'Jamaica',
            ],
            [
                'code' => 'America/Juneau',
                'name' => 'Juneau',
            ],
            [
                'code' => 'America/Kentucky/Louisville',
                'name' => 'Louisville',
            ],
            [
                'code' => 'America/Kentucky/Monticello',
                'name' => 'Monticello',
            ],
            [
                'code' => 'America/Kralendijk',
                'name' => 'Kralendijk',
            ],
            [
                'code' => 'America/La_Paz',
                'name' => 'La_Paz',
            ],
            [
                'code' => 'America/Lima',
                'name' => 'Lima',
            ],
            [
                'code' => 'America/Los_Angeles',
                'name' => 'Los_Angeles',
            ],
            [
                'code' => 'America/Lower_Princes',
                'name' => 'Lower_Princes',
            ],
            [
                'code' => 'America/Maceio',
                'name' => 'Maceio',
            ],
            [
                'code' => 'America/Managua',
                'name' => 'Managua',
            ],
            [
                'code' => 'America/Manaus',
                'name' => 'Manaus',
            ],
            [
                'code' => 'America/Marigot',
                'name' => 'Marigot',
            ],
            [
                'code' => 'America/Martinique',
                'name' => 'Martinique',
            ],
            [
                'code' => 'America/Matamoros',
                'name' => 'Matamoros',
            ],
            [
                'code' => 'America/Mazatlan',
                'name' => 'Mazatlan',
            ],
            [
                'code' => 'America/Menominee',
                'name' => 'Menominee',
            ],
            [
                'code' => 'America/Merida',
                'name' => 'Merida',
            ],
            [
                'code' => 'America/Metlakatla',
                'name' => 'Metlakatla',
            ],
            [
                'code' => 'America/Mexico_City',
                'name' => 'Mexico_City',
            ],
            [
                'code' => 'America/Miquelon',
                'name' => 'Miquelon',
            ],
            [
                'code' => 'America/Moncton',
                'name' => 'Moncton',
            ],
            [
                'code' => 'America/Monterrey',
                'name' => 'Monterrey',
            ],
            [
                'code' => 'America/Montevideo',
                'name' => 'Montevideo',
            ],
            [
                'code' => 'America/Montserrat',
                'name' => 'Montserrat',
            ],
            [
                'code' => 'America/Nassau',
                'name' => 'Nassau',
            ],
            [
                'code' => 'America/New_York',
                'name' => 'New_York',
            ],
            [
                'code' => 'America/Nipigon',
                'name' => 'Nipigon',
            ],
            [
                'code' => 'America/Nome',
                'name' => 'Nome',
            ],
            [
                'code' => 'America/Noronha',
                'name' => 'Noronha',
            ],
            [
                'code' => 'America/North_Dakota/Beulah',
                'name' => 'Beulah',
            ],
            [
                'code' => 'America/North_Dakota/Center',
                'name' => 'Center',
            ],
            [
                'code' => 'America/North_Dakota/New_Salem',
                'name' => 'New_Salem',
            ],
            [
                'code' => 'America/Nuuk',
                'name' => 'Nuuk',
            ],
            [
                'code' => 'America/Ojinaga',
                'name' => 'Ojinaga',
            ],
            [
                'code' => 'America/Panama',
                'name' => 'Panama',
            ],
            [
                'code' => 'America/Pangnirtung',
                'name' => 'Pangnirtung',
            ],
            [
                'code' => 'America/Paramaribo',
                'name' => 'Paramaribo',
            ],
            [
                'code' => 'America/Phoenix',
                'name' => 'Phoenix',
            ],
            [
                'code' => 'America/Port-au-Prince',
                'name' => 'Port-au-Prince',
            ],
            [
                'code' => 'America/Port_of_Spain',
                'name' => 'Port_of_Spain',
            ],
            [
                'code' => 'America/Porto_Velho',
                'name' => 'Porto_Velho',
            ],
            [
                'code' => 'America/Puerto_Rico',
                'name' => 'Puerto_Rico',
            ],
            [
                'code' => 'America/Punta_Arenas',
                'name' => 'Punta_Arenas',
            ],
            [
                'code' => 'America/Rainy_River',
                'name' => 'Rainy_River',
            ],
            [
                'code' => 'America/Rankin_Inlet',
                'name' => 'Rankin_Inlet',
            ],
            [
                'code' => 'America/Recife',
                'name' => 'Recife',
            ],
            [
                'code' => 'America/Regina',
                'name' => 'Regina',
            ],
            [
                'code' => 'America/Resolute',
                'name' => 'Resolute',
            ],
            [
                'code' => 'America/Rio_Branco',
                'name' => 'Rio_Branco',
            ],
            [
                'code' => 'America/Santarem',
                'name' => 'Santarem',
            ],
            [
                'code' => 'America/Santiago',
                'name' => 'Santiago',
            ],
            [
                'code' => 'America/Santo_Domingo',
                'name' => 'Santo_Domingo',
            ],
            [
                'code' => 'America/Sao_Paulo',
                'name' => 'Sao_Paulo',
            ],
            [
                'code' => 'America/Scoresbysund',
                'name' => 'Scoresbysund',
            ],
            [
                'code' => 'America/Sitka',
                'name' => 'Sitka',
            ],
            [
                'code' => 'America/St_Barthelemy',
                'name' => 'St_Barthelemy',
            ],
            [
                'code' => 'America/St_Johns',
                'name' => 'St_Johns',
            ],
            [
                'code' => 'America/St_Kitts',
                'name' => 'St_Kitts',
            ],
            [
                'code' => 'America/St_Lucia',
                'name' => 'St_Lucia',
            ],
            [
                'code' => 'America/St_Thomas',
                'name' => 'St_Thomas',
            ],
            [
                'code' => 'America/St_Vincent',
                'name' => 'St_Vincent',
            ],
            [
                'code' => 'America/Swift_Current',
                'name' => 'Swift_Current',
            ],
            [
                'code' => 'America/Tegucigalpa',
                'name' => 'Tegucigalpa',
            ],
            [
                'code' => 'America/Thule',
                'name' => 'Thule',
            ],
            [
                'code' => 'America/Thunder_Bay',
                'name' => 'Thunder_Bay',
            ],
            [
                'code' => 'America/Tijuana',
                'name' => 'Tijuana',
            ],
            [
                'code' => 'America/Toronto',
                'name' => 'Toronto',
            ],
            [
                'code' => 'America/Tortola',
                'name' => 'Tortola',
            ],
            [
                'code' => 'America/Vancouver',
                'name' => 'Vancouver',
            ],
            [
                'code' => 'America/Whitehorse',
                'name' => 'Whitehorse',
            ],
            [
                'code' => 'America/Winnipeg',
                'name' => 'Winnipeg',
            ],
            [
                'code' => 'America/Yakutat',
                'name' => 'Yakutat',
            ],
            [
                'code' => 'America/Yellowknife',
                'name' => 'Yellowknife',
            ],
            [
                'code' => 'Antarctica/Casey',
                'name' => 'Casey',
            ],
            [
                'code' => 'Antarctica/Davis',
                'name' => 'Davis',
            ],
            [
                'code' => 'Antarctica/DumontDUrville',
                'name' => 'DumontDUrville',
            ],
            [
                'code' => 'Antarctica/Macquarie',
                'name' => 'Macquarie',
            ],
            [
                'code' => 'Antarctica/Mawson',
                'name' => 'Mawson',
            ],
            [
                'code' => 'Antarctica/McMurdo',
                'name' => 'McMurdo',
            ],
            [
                'code' => 'Antarctica/Palmer',
                'name' => 'Palmer',
            ],
            [
                'code' => 'Antarctica/Rothera',
                'name' => 'Rothera',
            ],
            [
                'code' => 'Antarctica/Syowa',
                'name' => 'Syowa',
            ],
            [
                'code' => 'Antarctica/Troll',
                'name' => 'Troll',
            ],
            [
                'code' => 'Antarctica/Vostok',
                'name' => 'Vostok',
            ],
            [
                'code' => 'Arctic/Longyearbyen',
                'name' => 'Longyearbyen',
            ],
            [
                'code' => 'Asia/Aden',
                'name' => 'Aden',
            ],
            [
                'code' => 'Asia/Almaty',
                'name' => 'Almaty',
            ],
            [
                'code' => 'Asia/Amman',
                'name' => 'Amman',
            ],
            [
                'code' => 'Asia/Anadyr',
                'name' => 'Anadyr',
            ],
            [
                'code' => 'Asia/Aqtau',
                'name' => 'Aqtau',
            ],
            [
                'code' => 'Asia/Aqtobe',
                'name' => 'Aqtobe',
            ],
            [
                'code' => 'Asia/Ashgabat',
                'name' => 'Ashgabat',
            ],
            [
                'code' => 'Asia/Atyrau',
                'name' => 'Atyrau',
            ],
            [
                'code' => 'Asia/Baghdad',
                'name' => 'Baghdad',
            ],
            [
                'code' => 'Asia/Bahrain',
                'name' => 'Bahrain',
            ],
            [
                'code' => 'Asia/Baku',
                'name' => 'Baku',
            ],
            [
                'code' => 'Asia/Bangkok',
                'name' => 'Bangkok',
            ],
            [
                'code' => 'Asia/Barnaul',
                'name' => 'Barnaul',
            ],
            [
                'code' => 'Asia/Beirut',
                'name' => 'Beirut',
            ],
            [
                'code' => 'Asia/Bishkek',
                'name' => 'Bishkek',
            ],
            [
                'code' => 'Asia/Brunei',
                'name' => 'Brunei',
            ],
            [
                'code' => 'Asia/Chita',
                'name' => 'Chita',
            ],
            [
                'code' => 'Asia/Choibalsan',
                'name' => 'Choibalsan',
            ],
            [
                'code' => 'Asia/Colombo',
                'name' => 'Colombo',
            ],
            [
                'code' => 'Asia/Damascus',
                'name' => 'Damascus',
            ],
            [
                'code' => 'Asia/Dhaka',
                'name' => 'Dhaka',
            ],
            [
                'code' => 'Asia/Dili',
                'name' => 'Dili',
            ],
            [
                'code' => 'Asia/Dubai',
                'name' => 'Dubai',
            ],
            [
                'code' => 'Asia/Dushanbe',
                'name' => 'Dushanbe',
            ],
            [
                'code' => 'Asia/Famagusta',
                'name' => 'Famagusta',
            ],
            [
                'code' => 'Asia/Gaza',
                'name' => 'Gaza',
            ],
            [
                'code' => 'Asia/Hebron',
                'name' => 'Hebron',
            ],
            [
                'code' => 'Asia/Ho_Chi_Minh',
                'name' => 'Ho_Chi_Minh',
            ],
            [
                'code' => 'Asia/Hong_Kong',
                'name' => 'Hong_Kong',
            ],
            [
                'code' => 'Asia/Hovd',
                'name' => 'Hovd',
            ],
            [
                'code' => 'Asia/Irkutsk',
                'name' => 'Irkutsk',
            ],
            [
                'code' => 'Asia/Jakarta',
                'name' => 'Jakarta',
            ],
            [
                'code' => 'Asia/Jayapura',
                'name' => 'Jayapura',
            ],
            [
                'code' => 'Asia/Jerusalem',
                'name' => 'Jerusalem',
            ],
            [
                'code' => 'Asia/Kabul',
                'name' => 'Kabul',
            ],
            [
                'code' => 'Asia/Kamchatka',
                'name' => 'Kamchatka',
            ],
            [
                'code' => 'Asia/Karachi',
                'name' => 'Karachi',
            ],
            [
                'code' => 'Asia/Kathmandu',
                'name' => 'Kathmandu',
            ],
            [
                'code' => 'Asia/Khandyga',
                'name' => 'Khandyga',
            ],
            [
                'code' => 'Asia/Kolkata',
                'name' => 'Kolkata',
            ],
            [
                'code' => 'Asia/Krasnoyarsk',
                'name' => 'Krasnoyarsk',
            ],
            [
                'code' => 'Asia/Kuala_Lumpur',
                'name' => 'Kuala_Lumpur',
            ],
            [
                'code' => 'Asia/Kuching',
                'name' => 'Kuching',
            ],
            [
                'code' => 'Asia/Kuwait',
                'name' => 'Kuwait',
            ],
            [
                'code' => 'Asia/Macau',
                'name' => 'Macau',
            ],
            [
                'code' => 'Asia/Magadan',
                'name' => 'Magadan',
            ],
            [
                'code' => 'Asia/Makassar',
                'name' => 'Makassar',
            ],
            [
                'code' => 'Asia/Manila',
                'name' => 'Manila',
            ],
            [
                'code' => 'Asia/Muscat',
                'name' => 'Muscat',
            ],
            [
                'code' => 'Asia/Nicosia',
                'name' => 'Nicosia',
            ],
            [
                'code' => 'Asia/Novokuznetsk',
                'name' => 'Novokuznetsk',
            ],
            [
                'code' => 'Asia/Novosibirsk',
                'name' => 'Novosibirsk',
            ],
            [
                'code' => 'Asia/Omsk',
                'name' => 'Omsk',
            ],
            [
                'code' => 'Asia/Oral',
                'name' => 'Oral',
            ],
            [
                'code' => 'Asia/Phnom_Penh',
                'name' => 'Phnom_Penh',
            ],
            [
                'code' => 'Asia/Pontianak',
                'name' => 'Pontianak',
            ],
            [
                'code' => 'Asia/Pyongyang',
                'name' => 'Pyongyang',
            ],
            [
                'code' => 'Asia/Qatar',
                'name' => 'Qatar',
            ],
            [
                'code' => 'Asia/Qostanay',
                'name' => 'Qostanay',
            ],
            [
                'code' => 'Asia/Qyzylorda',
                'name' => 'Qyzylorda',
            ],
            [
                'code' => 'Asia/Riyadh',
                'name' => 'Riyadh',
            ],
            [
                'code' => 'Asia/Sakhalin',
                'name' => 'Sakhalin',
            ],
            [
                'code' => 'Asia/Samarkand',
                'name' => 'Samarkand',
            ],
            [
                'code' => 'Asia/Seoul',
                'name' => 'Seoul',
            ],
            [
                'code' => 'Asia/Shanghai',
                'name' => 'Shanghai',
            ],
            [
                'code' => 'Asia/Singapore',
                'name' => 'Singapore',
            ],
            [
                'code' => 'Asia/Srednekolymsk',
                'name' => 'Srednekolymsk',
            ],
            [
                'code' => 'Asia/Taipei',
                'name' => 'Taipei',
            ],
            [
                'code' => 'Asia/Tashkent',
                'name' => 'Tashkent',
            ],
            [
                'code' => 'Asia/Tbilisi',
                'name' => 'Tbilisi',
            ],
            [
                'code' => 'Asia/Tehran',
                'name' => 'Tehran',
            ],
            [
                'code' => 'Asia/Thimphu',
                'name' => 'Thimphu',
            ],
            [
                'code' => 'Asia/Tokyo',
                'name' => 'Tokyo',
            ],
            [
                'code' => 'Asia/Tomsk',
                'name' => 'Tomsk',
            ],
            [
                'code' => 'Asia/Ulaanbaatar',
                'name' => 'Ulaanbaatar',
            ],
            [
                'code' => 'Asia/Urumqi',
                'name' => 'Urumqi',
            ],
            [
                'code' => 'Asia/Ust-Nera',
                'name' => 'Ust-Nera',
            ],
            [
                'code' => 'Asia/Vientiane',
                'name' => 'Vientiane',
            ],
            [
                'code' => 'Asia/Vladivostok',
                'name' => 'Vladivostok',
            ],
            [
                'code' => 'Asia/Yakutsk',
                'name' => 'Yakutsk',
            ],
            [
                'code' => 'Asia/Yangon',
                'name' => 'Yangon',
            ],
            [
                'code' => 'Asia/Yekaterinburg',
                'name' => 'Yekaterinburg',
            ],
            [
                'code' => 'Asia/Yerevan',
                'name' => 'Yerevan',
            ],
            [
                'code' => 'Atlantic/Azores',
                'name' => 'Azores',
            ],
            [
                'code' => 'Atlantic/Bermuda',
                'name' => 'Bermuda',
            ],
            [
                'code' => 'Atlantic/Canary',
                'name' => 'Canary',
            ],
            [
                'code' => 'Atlantic/Cape_Verde',
                'name' => 'Cape_Verde',
            ],
            [
                'code' => 'Atlantic/Faroe',
                'name' => 'Faroe',
            ],
            [
                'code' => "Atlantic/Madeira\t",
                'name' => "Madeira\t",
            ],
            [
                'code' => 'Atlantic/Reykjavik',
                'name' => 'Reykjavik',
            ],
            [
                'code' => 'Atlantic/South_Georgia',
                'name' => 'South_Georgia',
            ],
            [
                'code' => 'Atlantic/St_Helena',
                'name' => 'St_Helena',
            ],
            [
                'code' => "Atlantic/Stanley\t",
                'name' => "Stanley\t",
            ],
            [
                'code' => 'Australia/Adelaide',
                'name' => 'Adelaide',
            ],
            [
                'code' => "Australia/Brisbane\t",
                'name' => "Brisbane\t",
            ],
            [
                'code' => 'Australia/Broken_Hill',
                'name' => 'Broken_Hill',
            ],
            [
                'code' => 'Australia/Currie',
                'name' => 'Currie',
            ],
            [
                'code' => 'Australia/Darwin',
                'name' => 'Darwin',
            ],
            [
                'code' => 'Australia/Eucla',
                'name' => 'Eucla',
            ],
            [
                'code' => 'Australia/Hobart',
                'name' => 'Hobart',
            ],
            [
                'code' => 'Australia/Lindeman',
                'name' => 'Lindeman',
            ],
            [
                'code' => 'Australia/Lord_Howe',
                'name' => 'Lord_Howe',
            ],
            [
                'code' => 'Australia/Melbourne',
                'name' => 'Melbourne',
            ],
            [
                'code' => 'Australia/Perth',
                'name' => 'Perth',
            ],
            [
                'code' => 'Australia/Sydney',
                'name' => 'Sydney',
            ],
            [
                'code' => 'Europe/Amsterdam',
                'name' => 'Amsterdam',
            ],
            [
                'code' => 'Europe/Andorra',
                'name' => 'Andorra',
            ],
            [
                'code' => 'Europe/Astrakhan',
                'name' => 'Astrakhan',
            ],
            [
                'code' => 'Europe/Athens',
                'name' => 'Athens',
            ],
            [
                'code' => 'Europe/Belgrade',
                'name' => 'Belgrade',
            ],
            [
                'code' => 'Europe/Berlin',
                'name' => 'Berlin',
            ],
            [
                'code' => 'Europe/Bratislava',
                'name' => 'Bratislava',
            ],
            [
                'code' => 'Europe/Brussels',
                'name' => 'Brussels',
            ],
            [
                'code' => 'Europe/Bucharest',
                'name' => 'Bucharest',
            ],
            [
                'code' => 'Europe/Budapest',
                'name' => 'Budapest',
            ],
            [
                'code' => 'Europe/Busingen',
                'name' => 'Busingen',
            ],
            [
                'code' => 'Europe/Chisinau',
                'name' => 'Chisinau',
            ],
            [
                'code' => 'Europe/Copenhagen',
                'name' => 'Copenhagen',
            ],
            [
                'code' => 'Europe/Dublin',
                'name' => 'Dublin',
            ],
            [
                'code' => 'Europe/Gibraltar',
                'name' => 'Gibraltar',
            ],
            [
                'code' => 'Europe/Guernsey',
                'name' => 'Guernsey',
            ],
            [
                'code' => 'Europe/Helsinki',
                'name' => 'Helsinki',
            ],
            [
                'code' => 'Europe/Isle_of_Man',
                'name' => 'Isle_of_Man',
            ],
            [
                'code' => 'Europe/Istanbul',
                'name' => 'Istanbul',
            ],
            [
                'code' => 'Europe/Jersey',
                'name' => 'Jersey',
            ],
            [
                'code' => 'Europe/Kaliningrad',
                'name' => 'Kaliningrad',
            ],
            [
                'code' => 'Europe/Kiev',
                'name' => 'Kiev',
            ],
            [
                'code' => 'Europe/Kirov',
                'name' => 'Kirov',
            ],
            [
                'code' => 'Europe/Lisbon',
                'name' => 'Lisbon',
            ],
            [
                'code' => 'Europe/Ljubljana',
                'name' => 'Ljubljana',
            ],
            [
                'code' => 'Europe/London',
                'name' => 'London',
            ],
            [
                'code' => 'Europe/Luxembourg',
                'name' => 'Luxembourg',
            ],
            [
                'code' => 'Europe/Madrid',
                'name' => 'Madrid',
            ],
            [
                'code' => 'Europe/Malta',
                'name' => 'Malta',
            ],
            [
                'code' => 'Europe/Mariehamn',
                'name' => 'Mariehamn',
            ],
            [
                'code' => 'Europe/Minsk',
                'name' => 'Minsk',
            ],
            [
                'code' => 'Europe/Monaco',
                'name' => 'Monaco',
            ],
            [
                'code' => 'Europe/Moscow',
                'name' => 'Moscow',
            ],
            [
                'code' => 'Europe/Oslo',
                'name' => 'Oslo',
            ],
            [
                'code' => 'Europe/Paris',
                'name' => 'Paris',
            ],
            [
                'code' => 'Europe/Podgorica',
                'name' => 'Podgorica',
            ],
            [
                'code' => 'Europe/Prague',
                'name' => 'Prague',
            ],
            [
                'code' => 'Europe/Riga',
                'name' => 'Riga',
            ],
            [
                'code' => 'Europe/Rome',
                'name' => 'Rome',
            ],
            [
                'code' => 'Europe/Samara',
                'name' => 'Samara',
            ],
            [
                'code' => 'Europe/San_Marino',
                'name' => 'San_Marino',
            ],
            [
                'code' => 'Europe/Sarajevo',
                'name' => 'Sarajevo',
            ],
            [
                'code' => 'Europe/Saratov',
                'name' => 'Saratov',
            ],
            [
                'code' => 'Europe/Simferopol',
                'name' => 'Simferopol',
            ],
            [
                'code' => 'Europe/Skopje',
                'name' => 'Skopje',
            ],
            [
                'code' => 'Europe/Sofia',
                'name' => 'Sofia',
            ],
            [
                'code' => 'Europe/Stockholm',
                'name' => 'Stockholm',
            ],
            [
                'code' => 'Europe/Tallinn',
                'name' => 'Tallinn',
            ],
            [
                'code' => 'Europe/Tirane',
                'name' => 'Tirane',
            ],
            [
                'code' => 'Europe/Ulyanovsk',
                'name' => 'Ulyanovsk',
            ],
            [
                'code' => 'Europe/Uzhgorod',
                'name' => 'Uzhgorod',
            ],
            [
                'code' => 'Europe/Vaduz',
                'name' => 'Vaduz',
            ],
            [
                'code' => 'Europe/Vatican',
                'name' => 'Vatican',
            ],
            [
                'code' => 'Europe/Vienna',
                'name' => 'Vienna',
            ],
            [
                'code' => 'Europe/Vilnius',
                'name' => 'Vilnius',
            ],
            [
                'code' => 'Europe/Volgograd',
                'name' => 'Volgograd',
            ],
            [
                'code' => 'Europe/Warsaw',
                'name' => 'Warsaw',
            ],
            [
                'code' => 'Europe/Zagreb',
                'name' => 'Zagreb',
            ],
            [
                'code' => 'Europe/Zaporozhye',
                'name' => 'Zaporozhye',
            ],
            [
                'code' => 'Europe/Zurich',
                'name' => 'Zurich',
            ],
            [
                'code' => 'Indian/Antananarivo',
                'name' => 'Antananarivo',
            ],
            [
                'code' => 'Indian/Chagos',
                'name' => 'Chagos',
            ],
            [
                'code' => 'Indian/Christmas',
                'name' => 'Christmas',
            ],
            [
                'code' => 'Indian/Cocos',
                'name' => 'Cocos',
            ],
            [
                'code' => 'Indian/Comoro',
                'name' => 'Comoro',
            ],
            [
                'code' => 'Indian/Kerguelen',
                'name' => 'Kerguelen',
            ],
            [
                'code' => 'Indian/Mahe',
                'name' => 'Mahe',
            ],
            [
                'code' => 'Indian/Maldives',
                'name' => 'Maldives',
            ],
            [
                'code' => 'Indian/Mauritius',
                'name' => 'Mauritius',
            ],
            [
                'code' => 'Indian/Mayotte',
                'name' => 'Mayotte',
            ],
            [
                'code' => 'Indian/Reunion',
                'name' => 'Reunion',
            ],
            [
                'code' => 'Pacific/Apia',
                'name' => 'Apia',
            ],
            [
                'code' => 'Pacific/Auckland',
                'name' => 'Auckland',
            ],
            [
                'code' => 'Pacific/Bougainville',
                'name' => 'Bougainville',
            ],
            [
                'code' => 'Pacific/Chatham',
                'name' => 'Chatham',
            ],
            [
                'code' => 'Pacific/Chuuk',
                'name' => 'Chuuk',
            ],
            [
                'code' => 'Pacific/Easter',
                'name' => 'Easter',
            ],
            [
                'code' => 'Pacific/Efate',
                'name' => 'Efate',
            ],
            [
                'code' => 'Pacific/Enderbury',
                'name' => 'Enderbury',
            ],
            [
                'code' => 'Pacific/Fakaofo',
                'name' => 'Fakaofo',
            ],
            [
                'code' => 'Pacific/Fiji',
                'name' => 'Fiji',
            ],
            [
                'code' => 'Pacific/Funafuti',
                'name' => 'Funafuti',
            ],
            [
                'code' => 'Pacific/Galapagos',
                'name' => 'Galapagos',
            ],
            [
                'code' => 'Pacific/Gambier',
                'name' => 'Gambier',
            ],
            [
                'code' => 'Pacific/Guadalcanal',
                'name' => 'Guadalcanal',
            ],
            [
                'code' => 'Pacific/Guam',
                'name' => 'Guam',
            ],
            [
                'code' => 'Pacific/Honolulu',
                'name' => 'Honolulu',
            ],
            [
                'code' => 'Pacific/Kiritimati',
                'name' => 'Kiritimati',
            ],
            [
                'code' => 'Pacific/Kosrae',
                'name' => 'Kosrae',
            ],
            [
                'code' => 'Pacific/Kwajalein',
                'name' => 'Kwajalein',
            ],
            [
                'code' => 'Pacific/Majuro',
                'name' => 'Majuro',
            ],
            [
                'code' => 'Pacific/Marquesas',
                'name' => 'Marquesas',
            ],
            [
                'code' => 'Pacific/Midway',
                'name' => 'Midway',
            ],
            [
                'code' => 'Pacific/Nauru',
                'name' => 'Nauru',
            ],
            [
                'code' => 'Pacific/Niue',
                'name' => 'Niue',
            ],
            [
                'code' => 'Pacific/Norfolk',
                'name' => 'Norfolk',
            ],
            [
                'code' => 'Pacific/Noumea',
                'name' => 'Noumea',
            ],
            [
                'code' => 'Pacific/Pago_Pago',
                'name' => 'Pago_Pago',
            ],
            [
                'code' => 'Pacific/Palau',
                'name' => 'Palau',
            ],
            [
                'code' => 'Pacific/Pitcairn',
                'name' => 'Pitcairn',
            ],
            [
                'code' => 'Pacific/Pohnpei',
                'name' => 'Pohnpei',
            ],
            [
                'code' => 'Pacific/Port_Moresby',
                'name' => 'Port_Moresby',
            ],
            [
                'code' => 'Pacific/Rarotonga',
                'name' => 'Rarotonga',
            ],
            [
                'code' => 'Pacific/Saipan',
                'name' => 'Saipan',
            ],
            [
                'code' => 'Pacific/Tahiti',
                'name' => 'Tahiti',
            ],
            [
                'code' => 'Pacific/Tarawa',
                'name' => 'Tarawa',
            ],
            [
                'code' => 'Pacific/Tongatapu',
                'name' => 'Tongatapu',
            ],
            [
                'code' => 'Pacific/Wake',
                'name' => 'Wake',
            ],
            [
                'code' => 'Pacific/Wallis',
                'name' => 'Wallis',
            ],
        ];
        DB::table('time_zones')->insert($timeZones);
    }
}
