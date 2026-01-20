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
        Schema::create('self_service_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->unique();
            $table->unsignedBigInteger('outlet_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('device_code')->nullable();
            $table->string('service_type')->nullable(); // Contoh: dryer_a, washer_b
            $table->boolean('device_status')->default(0)->comment('0: Belum Aktif/Gagal, 1: Berhasil Aktif');
            $table->timestamp('last_attempt_at')->nullable()->comment('Waktu terakhir mencoba aktivasi ke IoT');

            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('self_service_transactions');
    }
};
