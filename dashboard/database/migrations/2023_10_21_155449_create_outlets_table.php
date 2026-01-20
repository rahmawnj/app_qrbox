<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('owners')->onDelete('cascade'); // Relasi owner ke outlet
            $table->string('outlet_name')->nullable();
            $table->string('image')->nullable();
            $table->string('city_name')->nullable();
            $table->string('code')->unique();
            $table->string('address')->nullable();
            $table->boolean('status')->default(false);

            $table->decimal('service_fee_percentage', 4, 3)->default(0.100);
            $table->decimal('min_monthly_service_fee', 12, 2)->default(100000.00);
            $table->decimal('device_deposit_price', 15, 2)->default(500000.00)->comment('Harga jaminan (deposit) per unit perangkat yang dibebankan kepada outlet saat registrasi device baru.');

            $table->enum('timezone', ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])->default('Asia/Jakarta')->comment('Indonesian Time Zones: Asia/Jakarta (Western Indonesian Time), Asia/Makassar (Central Indonesian Time), Asia/Jayapura (Eastern Indonesian Time)');
            $table->json('latlong')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
