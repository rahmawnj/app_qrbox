<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Setting;
use App\Models\JobCategory;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\OwnerOutletSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            OwnerOutletSeeder::class,
        ]);

        // User::factory(1000)->create();

        $serviceTypes = [
            'LAUNDRY' => ['washer','dryer_a','dryer_b'],
            'TURNSTILE' => ['turnstile'],
            'DISPENSER' => ['dispenser_a','dispenser_b','dispenser_c','dispenser_d']
        ];

        $outletIds = [1, 2, 3, 4];
        $data = [];
        // for ($i=0; $i<1000; $i++) {
        //     $outletId = $outletIds[array_rand($outletIds)];
        //     $category = array_rand($serviceTypes);
        //     $serviceType = $serviceTypes[$category][array_rand($serviceTypes[$category])];

        //     $data[] = [
        //         'transaction_id' => null,
        //         'outlet_id' => $outletId,
        //         'device_code' => strtoupper(Str::random(6)),
        //         'service_type' => $serviceType,
        //         'activated_at' => now()->subDays(rand(0,30)),
        //         'status' => rand(0,1),
        //         'bypass_activation' => rand(0,1) ? now()->subDays(rand(0,30)) : null,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ];
        // }

        // DB::table('device_transactions')->insert($data);

    }
}
