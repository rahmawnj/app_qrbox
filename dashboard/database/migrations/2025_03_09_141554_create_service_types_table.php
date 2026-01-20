<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('service_types', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // Contoh: "Laundry Equipment"
        $table->json('items')->nullable();    // Kolom JSON untuk menyimpan daftar anak
        $table->timestamps();
    });

DB::table('service_types')->insert([
    [
        'name' => 'Laundry',
        'items' => json_encode([
            ['key' => 'washer', 'label' => 'Washer', 'has_duration' => false],
            ['key' => 'dryer_a', 'label' => 'Dryer A', 'has_duration' => true],
            ['key' => 'dryer_b', 'label' => 'Dryer B', 'has_duration' => true],
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Turnstile',
        'items' => json_encode([
            ['key' => 'turnstile', 'label' => 'Turnstile Gate', 'has_duration' => false]
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Dispenser',
        'items' => json_encode([
            ['key' => 'dispenser_a', 'label' => 'Dispenser A', 'has_duration' => true],
            ['key' => 'dispenser_b', 'label' => 'Dispenser B', 'has_duration' => true],
            ['key' => 'dispenser_c', 'label' => 'Dispenser C', 'has_duration' => true],
            ['key' => 'dispenser_d', 'label' => 'Dispenser D', 'has_duration' => true],
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};
