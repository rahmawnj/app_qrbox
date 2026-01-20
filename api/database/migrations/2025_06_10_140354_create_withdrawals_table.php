<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->integer('amount')->comment('Total dengan biaya penarikan');
            $table->integer('requested_amount')->comment('Jumlah yang diminta untuk ditarik');
            // $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->string('bank_name')->nullable()->comment('Nama bank tujuan penarikan');
            $table->string('bank_account_number')->nullable()->comment('Nomor rekening tujuan');
            $table->string('bank_account_holder_name')->nullable()->comment('Nama pemilik rekening tujuan');

            $table->integer('amount_before_fee')->nullable()->comment('Saldo sebelum dipotong biaya');
            $table->integer('withdrawal_fee')->nullable()->comment('Biaya admin penarikan');
            $table->integer('amount_after_fee')->nullable()->comment('Jumlah yang ditransfer setelah dipotong');

            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
