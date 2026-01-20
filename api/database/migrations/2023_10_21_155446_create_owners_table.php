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
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->string('brand_logo')->nullable();
            $table->string('brand_phone')->nullable();
            $table->text('brand_description')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->boolean('status')->default(false);

            // --- Kolom Kontrak Baru ---
            $table->date('contract_start')->nullable()->comment('Tanggal mulai kontrak kerjasama');
            $table->date('contract_end')->nullable()->comment('Tanggal berakhir kontrak kerjasama');
            $table->string('contract_number')->nullable()->unique()->comment('Nomor dokumen kontrak fisik/digital');
            // --------------------------
            $table->decimal('deposit_amount', 15, 2)->default(0)->comment('Jumlah deposit/jaminan dari owner');

            $table->string('bank_name')->nullable()->comment('Nama bank untuk penarikan dana');
            $table->string('bank_account_number')->nullable()->comment('Nomor rekening bank untuk penarikan dana');
            $table->string('bank_account_holder_name')->nullable()->comment('Nama pemilik rekening bank untuk penarikan dana');
            $table->decimal('balance', 15, 2)->default(0)->comment('Saldo bersih owner yang siap dicairkan');

            $table->json('receipt_config')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('code')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};
