<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kelompok;
use App\Models\Generus;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Kelompok::factory()->create([
            'name' => 'Baamang Selatan',
            'district' => 'Baamang',
            'city' => 'Sampit',
            'address' => 'Jl. Raya No. 1, Sampit',
        ]);

        Kelompok::factory()->create([
            'name' => 'Baamang Timur',
            'district' => 'Baamang',
            'city' => 'Sampit',
            'address' => 'Jl. Raya No. 1, Sampit',
        ]);

        Kelompok::factory()->create([
            'name' => 'Ketapang 1',
            'district' => 'Ketapang',
            'city' => 'Sampit',
            'address' => 'Jl. Raya No. 1, Sampit',
        ]);

        Generus::factory()->create([
            'full_name' => 'Ilyas Abdullah',
            'place_of_birth' => 'Sampit',
            'date_of_birth' => '1990-01-01',
            'gender' => 'Laki-laki',
            'blood_type' => 'A',
            'father_name' => 'Abdullah',
            'mother_name' => 'Siti Aminah',
            'address' => 'Jl. Raya No. 1, Sampit',
            'kelompok_id' => 1,
            'jenjang' => 'Caberawit',
        ]);

        Generus::factory()->create([
            'full_name' => 'Sayyid',
            'place_of_birth' => 'Sampit',
            'date_of_birth' => '2000-01-01',
            'gender' => 'Laki-laki',
            'blood_type' => 'B',
            'father_name' => 'Abdullah',
            'mother_name' => 'Siti Aminah',
            'address' => 'Jl. Raya No. 1, Sampit',
            'kelompok_id' => 2,
            'jenjang' => 'Pra-remaja',
        ]);

        Generus::factory()->create([
            'full_name' => 'Faiz',
            'place_of_birth' => 'Sampit',
            'date_of_birth' => '2010-01-01',
            'gender' => 'Laki-laki',
            'blood_type' => 'A',
            'father_name' => 'Abdullah',
            'mother_name' => 'Siti Aminah',
            'address' => 'Jl. Raya No. 1, Sampit',
            'kelompok_id' => 3,
            'jenjang' => 'Berkeluarga',
        ]);

        User::factory()->create([
            'name' => 'Hiluma Studio',
            'email' => 'info@hilumastudio.com',
            'role' => User::ROLE_ADMIN,
            'password' => bcrypt('1234567890'),
        ]);

        User::factory()->create([
            'name' => 'Hilal',
            'email' => 'hilal@gmail.com',
            'role' => User::ROLE_KELOMPOK,
            'password' => bcrypt('1234567890'),
            'kelompok_id' => 1, 
        ]);
        
        User::factory()->create([
            'name' => 'Habib',
            'email' => 'habib@gmail.com',
            'role' => User::ROLE_KELOMPOK,
            'password' => bcrypt('1234567890'),
            'kelompok_id' => 2, 
        ]);

        User::factory()->create([
            'name' => 'Dayat',
            'email' => 'dayat@gmail.com',
            'role' => User::ROLE_KELOMPOK,
            'password' => bcrypt('1234567890'),
            'kelompok_id' => 3, 
        ]);
    }
}
