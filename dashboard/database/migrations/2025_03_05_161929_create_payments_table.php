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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('outlet_id');
            $table->unsignedBigInteger('owner_id');

            // $table->enum('payment_method', ['member', 'non_member']);

            $table->integer('amount');
            $table->dateTime('payment_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->enum('timezone', ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])->default('Asia/Jakarta')->comment('Indonesian Time Zones: Asia/Jakarta (Western Indonesian Time), Asia/Makassar (Central Indonesian Time), Asia/Jayapura (Eastern Indonesian Time)');
            $table->integer('service_fee_amount')->comment('Nilai potongan (gross - net)');
            $table->decimal('service_fee_percentage', 4, 3)->comment('Persentase fee saat transaksi terjadi (misal 0.100)');
            // $table->string('channel_type');

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('cascade'); // Menambah foreign key
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');  // Menambah foreign key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
