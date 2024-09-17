<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SudoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Let create Sudo Admin
        User::create([
            'nom'=>'Sudo',
            'prenom'=>'Admin',
            'email'=>'sudo@admin.hostel',
            'tel'=>'+229 91461545',
            'picture'=>'blank_profile.png',
            'password'=>bcrypt('Avademes21@?'),
            'role'=>UserRoles::SUDO,
            'statut'=>UserStatus::ENABLE,
        ]);

    }
}