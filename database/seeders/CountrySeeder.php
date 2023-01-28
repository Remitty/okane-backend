<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('countries')->truncate();

        $countries = [
            [ 'name' => 'United States', 'short_code' => 'USA' ],
            [ 'name' => 'Argentina', 'short_code' => 'ARG' ],
            [ 'name' => 'Australia', 'short_code' => 'AUS'],
            [ 'name' => 'Bolivia', 'short_code' => 'BOL'],
            [ 'name' => 'Brazil', 'short_code' => 'BRA'],
            [ 'name' => 'Chile', 'short_code' => 'CHL'],
            [ 'name' => 'Colombia', 'short_code' => 'COL'],
            [ 'name' => 'Costa Rica', 'short_code' => 'CRI'],
            [ 'name' => 'Germany', 'short_code' => 'DEU'],
            [ 'name' => 'Dominican Republic', 'short_code' => 'DOM'],
            [ 'name' => 'Ecuador', 'short_code' => 'ECU'],
            [ 'name' => 'France', 'short_code' => 'FRA'],
            [ 'name' => 'Guatemala', 'short_code' => 'GTM'],
            [ 'name' => 'Honduras', 'short_code' => 'HND'],
            [ 'name' => 'Hungary', 'short_code' => 'HUN'],
            [ 'name' => 'Indonesia', 'short_code' => 'IDN'],
            [ 'name' => 'India', 'short_code' => 'IND'],
            [ 'name' => 'United Kingdom', 'short_code' => 'GBR'],
            [ 'name' => 'Israel', 'short_code' => 'ISR'],
            [ 'name' => 'Italy', 'short_code' => 'ITA'],
            [ 'name' => 'Japan', 'short_code' => 'JPN'],
            [ 'name' => 'Mexico', 'short_code' => 'MEX'],
            [ 'name' => 'Nicaragua', 'short_code' => 'NIC'],
            [ 'name' => 'Netherland', 'short_code' => 'NLD'],
            [ 'name' => 'Pakistan', 'short_code' => 'PAK'],
            [ 'name' => 'Panama', 'short_code' => 'PAN'],
            [ 'name' => 'Peru', 'short_code' => 'PER'],
            [ 'name' => 'Paraguay', 'short_code' => 'PRY'],
            [ 'name' => 'Singapore', 'short_code' => 'SGP'],
            [ 'name' => 'Salvador', 'short_code' => 'SLV'],
            [ 'name' => 'Sweden', 'short_code' => 'SWE'],
            [ 'name' => 'Uruguay', 'short_code' => 'URY'],
            [ 'name' => 'Venezuela', 'short_code' => 'VEN'],
            ];

        DB::table('countries')->insert($countries);

    }
}
