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
       Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->foreignId('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->string('device_status')->default('off');

            $table->foreignId('service_type_id')->nullable()->constrained('service_types')->onDelete('set null');

            $table->timestamp('bypass_activation')->nullable();
            $table->string('bypass_note')->nullable();

            // Menghapus menu_settings lama dan menambah 4 kolom JSON baru
            $table->json('option_1')->nullable();
            $table->json('option_2')->nullable();
            $table->json('option_3')->nullable();
            $table->json('option_4')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
