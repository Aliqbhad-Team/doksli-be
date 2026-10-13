<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('folders', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('name', 150);
        $table->uuid('parent_folder_id')->nullable();
        $table->uuid('unit_id')->nullable();
        $table->uuid('created_by');
        $table->dateTime('created_at')->useCurrent();

        // Pembuatan Foreign Key & Constraints
        $table->foreign('parent_folder_id')->references('id')->on('folders')
              ->nullOnDelete()->cascadeOnUpdate();
              
        $table->foreign('unit_id')->references('id')->on('units')
              ->nullOnDelete()->cascadeOnUpdate();
              
        $table->foreign('created_by')->references('id')->on('users')
              ->restrictOnDelete()->cascadeOnUpdate();
    });
}
};
