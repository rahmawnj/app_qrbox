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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            // $table->unsignedBigInteger('outlet_id')->nullable();
            $table->string('order_id')->nullable();

            $table->integer('amount')->comment('Harga bersih yang diterima owner setelah dipotong fee');
            $table->enum('type', ['payment', 'withdrawal'])->default('payment');

            $table->integer('gross_amount')->comment('Harga kotor/asli sebelum dipotong fee');
            $table->integer('service_fee_amount')->comment('Nilai potongan (gross - net)');
            $table->decimal('service_fee_percentage', 4, 3)->comment('Persentase fee saat transaksi terjadi (misal 0.100)');

            $table->enum('timezone', ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])->default('Asia/Jakarta')->comment('Indonesian Time Zones: Asia/Jakarta (Western Indonesian Time), Asia/Makassar (Central Indonesian Time), Asia/Jayapura (Eastern Indonesian Time)');
            $table->date('date');
            $table->time('time');
            $table->string('status');
            $table->timestamps();
            $table->text('notes')->nullable()->comment('Catatan tambahan atau alasan penolakan penarikan');

            $table->foreign('owner_id')
                ->references('id')->on('owners')
                ->onDelete('cascade');
            // $table->foreign('outlet_id')
            //     ->references('id')->on('outlets')
            //     ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
