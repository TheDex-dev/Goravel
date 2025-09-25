<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\EscortModel;

class EscortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $default = 200;
        $count = env('ESCORT_SEED_COUNT') ?: $default;

        $batchSize = 100;

        $kategoris = ['Polisi', 'Ambulans', 'Perorangan'];
        $genders = ['Laki-laki', 'Perempuan'];

        $created = 0;
        // Get Faker instance from the container (works across Laravel versions)
        $faker = app(\Faker\Generator::class);
        while ($created < $count) {
            $toCreate = min($batchSize, $count - $created);
            $rows = [];
            for ($i = 0; $i < $toCreate; $i++) {
                $kategori = $kategoris[array_rand($kategoris)];
                $gender = $genders[array_rand($genders)];

                if ($gender === 'Laki-laki') {
                    $nama_pengantar = $faker->firstNameMale() . ' ' . $faker->lastName();
                } else {
                    $nama_pengantar = $faker->firstNameFemale() . ' ' . $faker->lastName();
                }

                $nomor_hp = preg_replace('/[^0-9]/', '', $faker->phoneNumber());
                $nomor_hp = substr($nomor_hp, 0, 15);

                // Plate number simple pattern: ABC-1234
                $plat_nomor = strtoupper(substr(Str::random(3), 0, 3)) . '-' . rand(100, 9999);

                $nama_pasien = $faker->name();

                $rows[] = [
                    'kategori_pengantar' => $kategori,
                    'nama_pengantar' => $nama_pengantar,
                    'jenis_kelamin' => $gender,
                    'nomor_hp' => $nomor_hp,
                    'plat_nomor' => $plat_nomor,
                    'nama_pasien' => $nama_pasien,
                    'foto_pengantar' => null,
                    'submission_id' => (string) Str::uuid(),
                    'submitted_from_ip' => null,
                    'api_submission' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Use bulk insert for performance; model events will not fire.
            EscortModel::insert($rows);
            $created += $toCreate;
        }
    }
}
