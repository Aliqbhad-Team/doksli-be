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
        Schema::create('tags', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('name', 64);
            $table->string('created_by', 32)->nullable();
            $table->timestamp('created_dt')->nullable();
            $table->string('updated_by', 32)->nullable();
            $table->timestamp('updated_dt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
