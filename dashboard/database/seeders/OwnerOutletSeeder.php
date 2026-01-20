<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Device;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OwnerOutletSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $fakerEn = \Faker\Factory::create('en_US');

        DB::transaction(function () use ($faker, $fakerEn) {

            /** ================= SERVICE TYPES ================= */
            $serviceTypes = ServiceType::all()->keyBy('name');
            // Pastikan tipe laundry tersedia
            $laundryService = ServiceType::where('name', 'LIKE', '%laundry%')->first() ?? $serviceTypes->first();

            /** ================= DEFAULT OWNER (TAZAKA) ================= */
            $userDefault = User::create([
                'name' => 'Rahma',
                'email' => 'rahma@tazaka.com',
                'password' => Hash::make('rahma@tazaka.com'),
            ]);

            $ownerDefault = Owner::create([
                'user_id' => $userDefault->id,
                'code' => 'BR-TZK',
                'brand_name' => 'Tazaka',
                'brand_phone' => '08123456789',
                'brand_description' => 'Company Tazaka Default',
                'status' => true,
                'contract_number' => 'CONT/2024/TAZAKA',
                'contract_start' => now()->toDateString(),
                'contract_end' => now()->addYear()->toDateString(),
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_holder_name' => 'RAHMA TAZAKA',
                'balance' => rand(50, 300) * 10000,
            ]);

            $outletDefault = Outlet::create([
                'owner_id' => $ownerDefault->id,
                'outlet_name' => 'Tazaka - Bandung H Gofur',
                'code' => 'OUT-GOFUR',
                'address' => 'Jl. H. Gofur, Bandung',
                'city_name' => 'Bandung',
                'status' => true,
                'timezone' => 'Asia/Jakarta',
                'latlong' => json_encode(['lat' => -6.9175, 'lon' => 107.6191]),
            ]);

            /** KHUSUS TAZAKA: 1 DEVICE SAJA (DEV-WHNTZR) */
            $this->createSpecificDevice($outletDefault->id, $laundryService, 'DEV-WHNTZR');


            for ($i = 1; $i <= 3; $i++) {

               $randomEmail = $faker->unique()->safeEmail;
                $user = User::create([
                    'name'     => $faker->name,
                    'email'    => $randomEmail,
                    'password' => Hash::make($randomEmail),
                ]);

                $owner = Owner::create([
                    'user_id' => $user->id,
                    'code' => 'BR' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'brand_name' => ucfirst($faker->word) . ' ' . $faker->company,
                    'status' => true,
                    'contract_number' => 'CONT/' . date('Y') . '/' . strtoupper(Str::random(5)),
                    'contract_start' => now()->toDateString(),
                    'contract_end' => now()->addYear()->toDateString(),
                    'bank_name' => $fakerEn->randomElement(['BCA', 'Mandiri', 'BRI']),
                    'bank_account_number' => $faker->bankAccountNumber,
                    'bank_account_holder_name' => strtoupper($user->name),
                    'balance' => rand(30, 500) * 10000,
                    'deposit_amount' => rand(10, 200) * 10000,
                ]);

                $outlet = Outlet::create([
                    'owner_id' => $owner->id,
                    'outlet_name' => $owner->brand_name . ' - Outlet ' . $i,
                    'code' => 'OUT-' . strtoupper(Str::random(6)),
                    'address' => $faker->address,
                    'city_name' => 'Jakarta',
                    'status' => true,
                    'timezone' => 'Asia/Jakarta',
                    'latlong' => json_encode(['lat' => -6.2088, 'lon' => 106.8456]),
                ]);

                // Sisa device diletakkan di owner lain (10 device per outlet)
                $this->generateDevices($outlet->id, $serviceTypes);
            }
        });
    }

    /** Helper untuk membuat 1 device spesifik */
    private function createSpecificDevice(int $outletId, $service, string $code): void
    {
        $items = collect($service->items);
        $menus = [];

        for ($i = 0; $i < 4; $i++) {
            $item = $items[$i] ?? null;
            $menus["option_" . ($i + 1)] = [
                'name' => 'Laundry Menu ' . ($i + 1),
                'type' => $item['key'] ?? 'none',
                'price' => $item ? rand(5, 20) * 1000 : 0,
                'active' => $item ? true : false,
                'duration' => $item && $item['has_duration'] ? rand(30, 120) : 0,
                'description' => $item ? '75' : '-',
            ];
        }

        Device::create([
            'name' => 'Main Machine Laundry',
            'code' => $code,
            'outlet_id' => $outletId,
            'device_status' => 'off',
            'service_type_id' => $service->id,
            'option_1' => $menus['option_1'],
            'option_2' => $menus['option_2'],
            'option_3' => $menus['option_3'],
            'option_4' => $menus['option_4'],
        ]);
    }

    /** Generator device untuk owner lainnya */
    private function generateDevices(int $outletId, $serviceTypes): void
    {
        for ($d = 1; $d <= 10; $d++) {
            $service = $serviceTypes->random();
            $items = collect($service->items);
            $menus = [];

            for ($i = 0; $i < 4; $i++) {
                $item = $items[$i] ?? null;
                $menus["option_" . ($i + 1)] = [
                    'name' => 'Menu ' . ($i + 1),
                    'type' => $item['key'] ?? 'none',
                    'price' => $item ? rand(5, 20) * 1000 : 0,
                    'active' => $item ? true : false,
                    'duration' => $item && $item['has_duration'] ? rand(30, 120) : 0,
                    'description' => $item ? '75' : '-',
                ];
            }

            Device::create([
                'name' => 'Machine ' . $d,
                'code' => 'DEV-' . strtoupper(Str::random(8)),
                'outlet_id' => $outletId,
                'device_status' => 'off',
                'service_type_id' => $service->id,
                'option_1' => $menus['option_1'],
                'option_2' => $menus['option_2'],
                'option_3' => $menus['option_3'],
                'option_4' => $menus['option_4'],
            ]);
        }
    }
}
