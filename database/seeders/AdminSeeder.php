<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->truncate();
        DB::table('admins')->insert([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('123456'),
            'role' => Admin::ROLE[0],
        ]);
        DB::table('admins')->insert([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('123456'),
            'role' => Admin::ROLE[1],
        ]);
        DB::table('admins')->insert([
            'name' => 'Operator',
            'email' => 'operator@test.com',
            'password' => Hash::make('123456'),
            'password_crypt' => Crypt::encryptString('123456'),
            'role' => Admin::ROLE[2],
        ]);
    }
}
