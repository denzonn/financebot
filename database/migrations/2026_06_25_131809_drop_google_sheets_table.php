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
        Schema::dropIfExists('google_sheets');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('google_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('spreadsheet_id')->unique();
            $table->string('spreadsheet_name');
            $table->string('sheet_name')
                ->default('Transaksi');
            $table->text('spreadsheet_url')
                ->nullable();
            $table->timestamps();
        });
    }
};
